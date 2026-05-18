<!--
 - BAV -- German Bank Account Validator
 -
 - @author Claus-Justus Heine
 - @copyright 2025, 2026 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <div class="container flex flex-column">
    <NcTextField v-model="accountData.IBAN"
                 type="text"
                 name="bankAccountIBAN"
                 placeholder="DE48123456780123456789"
                 :label="t(appName, 'IBAN')"
                 @blur="onBlur('IBAN')"
    />
    <NcTextField v-model="accountData.BIC"
                 type="text"
                 name="bankAccountBIC"
                 placeholder="XYBLAHDEFXX"
                 :label="t(appName, 'BIC')"
                 @blur="onBlur('BIC')"
    />
    <NcTextField v-model="accountData.bankId"
                 type="text"
                 name="bankAccountBankId"
                 placeholder="12345678"
                 :label="t(appName, 'Bank Id')"
                 @blur="onBlur('bankId')"
    />
    <NcTextField v-model="accountData.accountId"
                 type="text"
                 name="bankAccountId"
                 placeholder="0123456789"
                 :label="t(appName, 'Bank Account Id')"
                 @blur="onBlur('accountId')"
    />
    <NcTextField v-model="accountData.bankName"
                 type="text"
                 readonly
                 name="bankAccountBankName"
                 placeholder=""
                 :label="t(appName, 'Bank')"
    />
  </div>
</template>

<script setup lang="ts">
import type { BankAccountData } from './bank-account.d.ts'

import { translate as t } from '@nextcloud/l10n'
import {
  NcTextField,
} from '@nextcloud/vue'
import {
  reactive,
  watch,
} from 'vue'
import { appName } from './config.ts'

const props = defineProps<{ bankAccount: BankAccountData }>()

const emit = defineEmits(['update:bankAccount', 'blur:accountField'])

console.info('BAV PROPS', { props })

const accountData = reactive(props.bankAccount)

const onBlur = (key: string) => {
  emit('blur:accountField', { [key]: accountData[key] })
}

const accountKeys: (keyof BankAccountData)[] = Object.keys(props.bankAccount).filter((key) => !key.startsWith('_')) as (keyof BankAccountData)[]

watch(props, () => {
  for (const key of accountKeys) {
    if (accountData[key] !== props.bankAccount[key]) {
      console.info('BAV PROPS WATCHER', { key, old: accountData[key], new: props.bankAccount[key] })
      accountData[key] = props.bankAccount[key]
    }
  }
})

const emitUpdate = (key: keyof BankAccountData) => {
  emit('update:bankAccount', { changed: key, data: accountData })
}

for (const key of accountKeys) {
  watch(() => accountData[key], () => emitUpdate(key))
}

</script>

<style scoped lang="scss">
  /* nothing */
</style>
