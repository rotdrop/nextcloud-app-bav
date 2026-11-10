import type { BankAccountData } from '../../src/bank-account.d.ts';

import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import BankAccountInputMask from '../../src/BankAccountInputMask.vue';

const makeAccount = (): BankAccountData => ({
  IBAN: 'DE89370400440532013000',
  BIC: 'COBADEFFXXX',
  bankId: '37040044',
  accountId: '0532013000',
  bankName: 'Example bank',
});

const fields = [
  ['IBAN', 'bankAccountIBAN'],
  ['BIC', 'bankAccountBIC'],
  ['bankId', 'bankAccountBankId'],
  ['accountId', 'bankAccountId'],
] as const;

describe('BankAccountInputMask', () => {
  it('renders account values and keeps the bank name read-only', () => {
    const bankAccount = makeAccount();
    const wrapper = mount(BankAccountInputMask, { props: { bankAccount } });

    expect(wrapper.findAll('input')).toHaveLength(5);
    for (const [key, name] of [...fields, ['bankName', 'bankAccountBankName']] as const) {
      expect(wrapper.get<HTMLInputElement>(`input[name="${name}"]`).element.value).toBe(bankAccount[key]);
    }
    expect(wrapper.get('input[name="bankAccountBankName"]').attributes()).toHaveProperty('readonly');
    expect(wrapper.emitted('update:bankAccount')).toBeUndefined();
  });

  it.each(fields)('emits the changed field and complete account when editing %s', async (key, name) => {
    const bankAccount = makeAccount();
    const wrapper = mount(BankAccountInputMask, { props: { bankAccount } });

    await wrapper.get(`input[name="${name}"]`).setValue('updated');

    expect(wrapper.emitted('update:bankAccount')).toEqual([
      [{ changed: key, data: { ...bankAccount, [key]: 'updated' } }],
    ]);
  });

  it.each(fields)('emits only the blurred field for %s', async (key, name) => {
    const bankAccount = makeAccount();
    const wrapper = mount(BankAccountInputMask, { props: { bankAccount } });

    await wrapper.get(`input[name="${name}"]`).trigger('blur');

    expect(wrapper.emitted('blur:accountField')).toEqual([[{ [key]: bankAccount[key] }]]);
  });

  it('updates displayed fields when the parent replaces the account', async () => {
    const wrapper = mount(BankAccountInputMask, { props: { bankAccount: makeAccount() } });
    const updated = { ...makeAccount(), IBAN: '', bankName: 'Another bank' };

    await wrapper.setProps({ bankAccount: updated });

    expect(wrapper.get<HTMLInputElement>('input[name="bankAccountIBAN"]').element.value).toBe('');
    expect(wrapper.get<HTMLInputElement>('input[name="bankAccountBankName"]').element.value).toBe('Another bank');
  });
});
