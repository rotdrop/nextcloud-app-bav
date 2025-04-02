<!--
 - BAV -- German Bank Account Validator
 -
 - @author Claus-Justus Heine
 - @copyright 2025 Claus-Justus Heine <himself@claus-justus-heine.de>
 -
 - This library is free software; you can redistribute it and/or
 - modify it under the terms of the GNU GENERAL PUBLIC LICENSE
 - License as published by the Free Software Foundation; either
 - version 3 of the License, or any later version.
 -
 - This library is distributed in the hope that it will be useful,
 - but WITHOUT ANY WARRANTY; without even the implied warranty of
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 - GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 -
 - You should have received a copy of the GNU Lesser General Public
 - License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 -->
<template>
  <!-- :has-next="false"
       :has-prev="false"
       container="#body-user"
  -->
  <NcDialog :name="t(appName, 'BAV - Bank Account Validator (DE)')"
            size="large"
            :close-on-click-outside="false"
            :open.sync="showDialog"
  >
    <template #actions>
      <NcButton :disabled="atHistoryBottom"
                @click="stateHistoryGo(-1)"
      >
        {{ t(appName, 'Undo') }}
      </NcButton>
      <NcButton :disabled="atHistoryTop"
                @click="stateHistoryGo(1)"
      >
        {{ t(appName, 'Redo') }}
      </NcButton>
      <NcButton :disabled="emptyContent"
                @click="clearForm"
      >
        {{ t(appName, 'Clear') }}
      </NcButton>
      <NcPopover :shown.sync="showAbout"
                 :focus-trap="false"
      >
        <template #trigger>
          <NcButton @click="showAbout = true">
            {{ t(appName, 'About') }}
          </NcButton>
        </template>
        <div class="about">
          {{ about.title }}
        </div>
        <div class="about">
          {{ about.backendStatus }}
        </div>
        <div class="about">
          {{ about.policy }}
        </div>
        <div class="about checksum">
          {{ about.checksumWarning }}
        </div>
        <div class="about disclaimer">
          {{ about.disclaimer }}
        </div>
      </NcPopover>
      <NcButton @click="showDialog = false">
        {{ t(appName, 'Close') }}
      </NcButton>
    </template>
    <template #default>
      <DynamicSvgIcon class="dialog-app-icon"
                      :size="34"
                      :data="appIcon"
                      :title="t(appName, 'app-logo')"
      />
      <BankAccountInputMask ref="inputForm"
                            :bank-account="accountData"
                            @update:bankAccount="onUpdateBankAccount"
                            @blur:account-field="onBlur"
      />
      <h6 v-if="hints.length > 0" class="hints">
        {{ t(appName, 'Errors and Hints') }}
      </h6>
      <!-- eslint-disable-next-line vue/require-v-for-key -->
      <span v-for="hint in hints" class="hint">{{ hint }}.&nbsp;</span>
      <h6 v-if="suggestionsLength === 1">
        {{ t(appName, 'Suggestion') }}
      </h6>
      <h6 v-if="suggestionsLength > 1">
        {{ t(appName, 'Suggestions') }}
      </h6>
      <ul v-if="suggestionsLength > 0">
        <li v-for="(parts, iban) in suggestions"
            :key="iban"
            class="suggestion"
        >
          <button @click.prevent.stop="selectSuggestion(iban)">
            {{ t(appName, 'use') }}
            &nbsp;
            <!-- eslint-disable-next-line vue/require-v-for-key -->
            <span v-for="part in parts"
                  :class="['iban-part', { 'part-equal': part.equal, 'part-different': !part.equal }]"
            >{{ part.str }}</span>
          </button>
        </li>
      </ul>
    </template>
  </NcDialog>
</template>
<script setup lang="ts">
import { appName } from './config.ts'
import { generateUrl as generateAppUrl } from './toolkit/util/generate-url.ts'
import { translate as t } from '@nextcloud/l10n'
import axios from '@nextcloud/axios'
import {
  NcButton,
  NcDialog,
  NcPopover,
} from '@nextcloud/vue'
import DynamicSvgIcon from '@rotdrop/nextcloud-vue-components/lib/components/DynamicSvgIcon.vue'
import { showError } from '@nextcloud/dialogs'
import {
  computed,
  onMounted,
  onUnmounted,
  reactive,
  ref,
} from 'vue'
import BankAccountInputMask from './BankAccountInputMask.vue'
import {
  isAxiosError,
  isAxiosErrorResponse,
} from './toolkit/types/axios-type-guards.ts'
import type { BankAccountData } from './bank-account.d.ts'
import appIcon from '../img/bav-color.svg?raw'

const showDialog = ref(true)

const showAbout = ref(false)

const inputForm = ref<null | Vue>(null)

const hints = ref<string[]>([])
interface EqualityPart {
  str: string,
  equal: boolean,
  start: number,
  len: number,
}
const suggestions = ref<Record<string, EqualityPart[]>>({})
const suggestionsLength = computed(() => Object.keys(suggestions.value).length)

const accountDataDefaults: BankAccountData = {
  IBAN: '',
  BIC: '',
  bankId: '',
  accountId: '',
  bankName: '',
}

const accountDataKeys: (keyof BankAccountData)[] = Object.keys(accountDataDefaults) as (keyof BankAccountData)[]

const accountData = reactive<BankAccountData>({ ...accountDataDefaults })

// All fields are empty
const isEmptyData = (data: BankAccountData) =>
  accountDataKeys.reduce((accu, key) => accu && !data[key], true)

// All fields are non-empty
const isFilledData = (data: BankAccountData) =>
  accountDataKeys.reduce((accu, key) => accu && !!data[key], true)

const emptyContent = computed(() => isEmptyData(accountData))

const historyData = ref<BankAccountData[]>([])
const historyPosition = ref(-1)
const atHistoryBottom = computed(() => historyPosition.value <= 0)
const atHistoryTop = computed(() => historyPosition.value >= historyData.value.length - 1)
const currentHistory = computed(
  () => historyPosition.value >= 0 && historyPosition.value < historyData.value.length
    ? historyData.value[historyPosition.value]
    : null,
)

const pushHistory = (data: BankAccountData) => {
  if (isEmptyData(data)) {
    return
  }
  if (currentHistory.value) {
    let equal = true
    for (const key of accountDataKeys) {
      if (currentHistory.value[key] !== data[key]) {
        equal = false
        break
      }
    }
    if (equal) {
      return
    }
  }
  historyData.value.splice(historyPosition.value + 1, Infinity, { ...data })
  ++historyPosition.value
}

const clearForm = () => {
  Object.assign(accountData, accountDataDefaults)
}

const stateHistoryGo = (delta: number) => {
  historyPosition.value += delta
  if (historyData.value.length > 0 && historyPosition.value < 0) {
    historyPosition.value = 0
  } else if (historyPosition.value >= historyData.value.length) {
    historyPosition.value = historyData.value.length - 1
  }
  if (currentHistory.value) {
    Object.assign(accountData, currentHistory.value)
  } else {
    // should not happen ...
    clearForm()
  }
  validateInput({ data: accountData, changed: null, liveUpdate: false })
}

const validationUrl = generateAppUrl('validate')

interface ValidateResponse extends BankAccountData {
  messages: string[],
  suggestions: Record<string, string>,
}

let abortController = new AbortController()

const human = (value: string, partLen = 4) => {
  const re = new RegExp('.{1,' + partLen + '}', 'gs')
  return value.match(re)!.join(' ')
}

const splitSuggestion = (suggestion: string, iban: string) => {
  const parts: EqualityPart[] = []
  let start = 0
  let len = 0
  let equal = true
  iban = human(iban.replace(' ', ''))
  for (let i = 0; i < Math.min(suggestion.length, iban.length); ++i) {
    if ((suggestion[i] === iban[i]) === equal) {
      ++len
    } else {
      const part = {
        str: suggestion.substring(start, start + len),
        equal,
        start,
        len,
      }
      parts.push(part)
      equal = !equal
      start = i
      len = 1
    }
  }
  if (len > 0) {
    const part = {
      str: suggestion.substring(start),
      equal,
      start,
      len,
    }
    parts.push(part)
  }
  return parts
}

const selectSuggestion = (iban: string) => {
  Object.assign(accountData, accountDataDefaults)
  accountData.IBAN = iban
}

interface ValidateInputArgs {
  data: BankAccountData,
  changed: keyof BankAccountData|null,
  liveUpdate: boolean,
}

const validateInput = async ({ data, changed, liveUpdate }: ValidateInputArgs) => {
  try {
    abortController.abort()
    abortController = new AbortController()
    const response = await axios.post<ValidateResponse>(
      validationUrl,
      data,
      {
        signal: abortController.signal,
      },
    )
    for (const key of Object.keys(accountDataDefaults)) {
      if (liveUpdate && key === changed) {
        continue // do not interrupt user input
      }
      if (accountData[key] !== response.data[key]) {
        accountData[key] = response.data[key]
      }
    }
    hints.value = response.data.messages
    suggestions.value = Object.fromEntries(
      Object.entries(response.data.suggestions).map(([machine, human]) => [machine, splitSuggestion(human, accountData.IBAN)]),
    )
    if (!liveUpdate || (isFilledData(accountData) && Object.keys(suggestions.value).length === 0)) {
      pushHistory(accountData)
    }
  } catch (e) {
    if (isAxiosError(e) && e.name === 'CanceledError') {
      console.debug('VALIDATION CANCELED BY USER INPUT')
    } else {
      console.error('BAV VALIDATION ERROR', { error: e })
      let message = t(appName, 'reason unknown')
      if (isAxiosErrorResponse(e) && e.response.data) {
        const responseData = e.response.data as { messages?: string[] }
        if (Array.isArray(responseData.messages)) {
          message = responseData.messages.join(' ')
        }
      }
      showError(t(appName, 'Unable to validate the given bank account data: {message}', {
        message,
      }))
    }
  }
}

interface UpdateBankAccountEvent {
  data: BankAccountData,
  changed: keyof BankAccountData,
}

const onUpdateBankAccount = async (event: UpdateBankAccountEvent) => {
  validateInput({ data: event.data, changed: event.changed, liveUpdate: true }) // just autocomplete, do not complain, perhaps add suggestions
}

const onBlur = async (event: Record<keyof BankAccountData, string>) => {
  const data = { ...accountData, ...event }
  const changed = Object.keys(event)[0] as keyof BankAccountData
  await validateInput({ data, changed, liveUpdate: false })
}

const setVisibility = (visible: boolean) => {
  showDialog.value = visible
}

const getVisibility = () => showDialog.value

let mounted = false

onMounted(() => { mounted = true })
onUnmounted(() => { mounted = false })

const getMounted = () => mounted

defineExpose({
  setVisibility,
  getVisibility,
  getMounted,
})

// Vue has problems with literal texts, so hack around those Vue deficiencies
const about = reactive({
  title: t(appName, 'This is a front-end to the German Bank Account Validator (BAV) developed my M. Malkusch.'),
  backendStatus: t(
    appName,
    `The backend is no longer actively maintained, but there are some forks on GitHub ({github}).
However, it still seems to work for the majority of German bank accounts.`,
    { github: 'https://github.com/bav-php/bav/forks' },
  ),
  policy: t(appName, `Data entered will not be stored. BAV is able to compute
the BIC given the bank-id, and to compute the IBAN
given additionally the bank-account id.
Additionally, it verifies that the bank-id exists
by comparing it with the data provided by the Deutsche Bundesbank.
It will also pick the correct check-sum formula for the given
bank-account id (there are a few hundrets of possible
check-sum formulas, differing from bank to bank, all archived and publically
available at the Deutsche Bundesbank).
Finally, given an IBAN, it will extract the bank-id and bank-account-id
and also perform the check-sum computations in order to test whether the
provided bank-account data is consistent.`),
  checksumWarning: t(appName, `Please keep in mind that check-sums
cannot catch all possible errors.`),
  disclaimer: t(
    appName,
    `This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see {gnuLicenses}.`,
    { gnuLicenses: 'http://www.gnu.org/licenses/' },
  ),
})
</script>
<style scoped lang="scss">
.suggestion {
  .iban-part.part-different {
    color: red;
    font-weight: bold;
  }
}
.dialog-app-icon {
  position: absolute;
  left: 0;
  top: 0;
  padding-block: 4px 0;
  padding-inline: 12px 0;
}
.about {
  max-width: 60ex;
  margin: 1ex;
  &.checksum {
    font-style: italic;
  }
  &.disclaimer {
    font-weight: bold;
    font-style: italic;
  }
}
</style>
