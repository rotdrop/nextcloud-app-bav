import type { BankAccountData } from '../../src/bank-account.d.ts';

import axios from '@nextcloud/axios';
import { flushPromises, mount } from '@vue/test-utils';
import { AxiosError, CanceledError } from 'axios';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import App from '../../src/App.vue';
import BankAccountInputMask from '../../src/BankAccountInputMask.vue';
import { showError } from '../../src/toolkit/util/toasts.ts';

const emptyAccount: BankAccountData = {
  IBAN: '',
  BIC: '',
  bankId: '',
  accountId: '',
  bankName: '',
};
const validAccount: BankAccountData = {
  IBAN: 'DE89370400440532013000',
  BIC: 'COBADEFFXXX',
  bankId: '37040044',
  accountId: '0532013000',
  bankName: 'Example bank',
};
const post = vi.mocked(axios.post);
const renderApp = () => mount(App, {
  global: { stubs: { BankAccountInputMask: true } },
});
type AppWrapper = ReturnType<typeof renderApp>;
const form = (wrapper: AppWrapper) => wrapper.getComponent(BankAccountInputMask);
const button = (wrapper: AppWrapper, label: string) => {
  const found = wrapper.findAll('button').find((candidate) => candidate.text() === label);
  if (!found) {
    throw new Error(`Missing button: ${label}`);
  }
  return found;
};
const reply = (account = validAccount, messages: string[] = [], suggestions: Record<string, string> = {}) => ({
  data: { ...account, messages, suggestions },
});
const blur = async (wrapper: AppWrapper, IBAN: string) => {
  form(wrapper).vm.$emit('blur:accountField', { IBAN });
  await flushPromises();
};

beforeEach(() => {
  post.mockReset();
  post.mockResolvedValue(reply());
});

describe('App', () => {
  it('opens with an empty form and disabled history/clear actions without requesting validation', () => {
    const wrapper = renderApp();

    expect(wrapper.get('[role="dialog"]').attributes('aria-label')).toBe('BAV - Bank Account Validator (DE)');
    expect(form(wrapper).props('bankAccount')).toEqual(emptyAccount);
    for (const label of ['Undo', 'Redo', 'Clear']) {
      expect(button(wrapper, label).attributes()).toHaveProperty('disabled');
    }
    expect(post).not.toHaveBeenCalled();
  });

  it('closes and can be reopened through its public visibility API', async () => {
    const wrapper = renderApp();

    await button(wrapper, 'Close').trigger('click');
    expect(wrapper.vm.getVisibility()).toBe(false);
    expect(wrapper.find('[role="dialog"]').exists()).toBe(false);

    wrapper.vm.setVisibility(true);
    await wrapper.vm.$nextTick();
    expect(wrapper.vm.getVisibility()).toBe(true);
    expect(wrapper.find('[role="dialog"]').exists()).toBe(true);
  });

  it('shows the about text on demand', async () => {
    const wrapper = renderApp();
    expect(wrapper.find('.about').exists()).toBe(false);
    await button(wrapper, 'About').trigger('click');
    expect(wrapper.get('.about.checksum').text()).toContain('cannot catch all possible errors');
  });

  it('autocompletes live input without overwriting the field being edited', async () => {
    const wrapper = renderApp();
    const data = form(wrapper).props('bankAccount');
    data.IBAN = 'DE89';
    form(wrapper).vm.$emit('update:bankAccount', { data, changed: 'IBAN' });
    await flushPromises();

    expect(post).toHaveBeenCalledWith('/apps/bav/validate', data, { signal: expect.any(AbortSignal) });
    expect(form(wrapper).props('bankAccount')).toEqual({ ...validAccount, IBAN: 'DE89' });

    await blur(wrapper, 'DE89');
    expect(form(wrapper).props('bankAccount')).toEqual(validAccount);
  });

  it('renders validation hints and suggestions and uses a selected IBAN', async () => {
    post.mockResolvedValue(reply(
      { ...emptyAccount, IBAN: 'DE00370400440532013000' },
      ['Invalid checksum'],
      { [validAccount.IBAN]: 'DE89 3704 0044 0532 0130 00' },
    ));
    const wrapper = renderApp();
    await blur(wrapper, 'DE00370400440532013000');

    expect(wrapper.get('.hint').text()).toContain('Invalid checksum');
    expect(wrapper.get('.part-different').text()).toBe('89');
    expect(wrapper.findAll('.iban-part').map((part) => part.element.textContent).join('')).toBe('DE89 3704 0044 0532 0130 00');

    await wrapper.get('.suggestion button').trigger('click');
    expect(form(wrapper).props('bankAccount')).toEqual({ ...emptyAccount, IBAN: validAccount.IBAN });
  });

  it('restores validated history with undo/redo and clears the form', async () => {
    const wrapper = renderApp();
    await blur(wrapper, validAccount.IBAN);
    const second = { ...validAccount, IBAN: 'DE12500105170648489890', bankId: '50010517' };
    post.mockResolvedValueOnce(reply(second));
    await blur(wrapper, second.IBAN);

    post.mockResolvedValueOnce(reply(validAccount));
    await button(wrapper, 'Undo').trigger('click');
    await flushPromises();
    expect(form(wrapper).props('bankAccount')).toEqual(validAccount);
    expect(button(wrapper, 'Undo').attributes()).toHaveProperty('disabled');
    expect(button(wrapper, 'Redo').attributes('disabled')).toBeUndefined();

    post.mockResolvedValueOnce(reply(second));
    await button(wrapper, 'Redo').trigger('click');
    await flushPromises();
    expect(form(wrapper).props('bankAccount')).toEqual(second);
    expect(button(wrapper, 'Redo').attributes()).toHaveProperty('disabled');

    await button(wrapper, 'Clear').trigger('click');
    expect(form(wrapper).props('bankAccount')).toEqual(emptyAccount);
    expect(button(wrapper, 'Clear').attributes()).toHaveProperty('disabled');
  });

  it('aborts the previous validation request when new input arrives', async () => {
    const wrapper = renderApp();
    await blur(wrapper, 'DE89');
    const firstSignal = post.mock.calls[0][2]!.signal!;
    expect(firstSignal.aborted).toBe(false);

    await blur(wrapper, validAccount.IBAN);
    expect(firstSignal.aborted).toBe(true);
    expect(post.mock.calls[1][2]!.signal!.aborted).toBe(false);
  });

  it('reports validation failures through an error toast', async () => {
    vi.spyOn(console, 'error').mockImplementation(() => {});
    post.mockRejectedValueOnce(new AxiosError('Network unavailable'));
    const wrapper = renderApp();

    await blur(wrapper, 'DE89');

    expect(showError).toHaveBeenCalledWith('Unable to validate the given bank account data: reason unknown');
    expect(form(wrapper).props('bankAccount')).toEqual(emptyAccount);
  });

  it('does not report canceled requests as validation errors', async () => {
    post.mockRejectedValueOnce(new CanceledError());
    const wrapper = renderApp();
    await blur(wrapper, 'DE89');
    expect(showError).not.toHaveBeenCalled();
  });
});
