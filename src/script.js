/**
 * BAV -- German Bank Account Validator
 *
 * @author Claus-Justus Heine
 * @copyright 2014-2021 Claus-Justus Heine <himself@claus-justus-heine.de>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

import { appName } from './config.js';
import generateUrl from './generate-url.js';
import 'bootstrap/js/dist/tooltip';

const jQuery = require('jquery');
const $ = jQuery;

require('style.css');

require('jquery-ui');
// require('jquery-ui/ui/effect');
require('jquery-ui/ui/widgets/dialog');
require('./nextcloud/jquery/requesttoken.js');

console.info('JQUERY', $.fn.jquery);

$(function () {

  /**
   * Post to get application value, call callback with default value
   * on failure or unset, else with configured value.
   *
   * @param {String} key TBD.
   *
   * @param {String} defaultValue TBD.
   *
   * @param {Function} callback TBD.
   */
  const getAppValue = function(key, defaultValue, callback)
  {
    $.get(generateUrl('settings/admin/get/modal/' + defaultValue))
      .done(function(data) {
        callback(data.value || defaultValue);
      })
      .fail(function(jqXHR) {
        callback(defaultValue);
      });
  };

  const appButton = $('li[data-id="' + appName + '"] a');
  appButton.click(function(event) {
    if ($('#bav-container').length > 0) {
      console.info('already open');
      return false;
    }

    getAppValue('modal', true, function(modal) {
      console.info('modal', modal);

      $.post(generateUrl(appName), {})
        .done(function(result) {
          const dialogHolder = $('<div id="bav-container"></div>');
          dialogHolder.html(result);

          let clearing = false;
          let validating = false;
          dialogHolder.dialog({
            title: t(appName, 'BAV - Bank Account Validator (DE)'),
            position: {
              my: 'center center',
              at: 'center-5% center-10%',
              of: window,
            },
            width: 'auto',
            height: 'auto',
            modal: modal,
            closeOnEscape: false,
            dialogClass: appName,
            resizable: false,
            buttons: [
              {
                text: t(appName, 'Clear'),
                class: 'clear',
                title: t(appName, 'Clear all fields of the input form'),
                click() {
                  clearing = true;
                  dialogHolder.find('input.bankAccountBIC').val('');
                  dialogHolder.find('input.bankAccountIBAN').val('');
                  dialogHolder.find('input.bankAccountBankId').val('');
                  dialogHolder.find('input.bankAccountId').val('');
                  dialogHolder.find('input.bankAccountBankName').val('');
                  dialogHolder.find('div.bav-status').html('&nbsp;');
                  if (!validating) {
                    clearing = false;
                  }
                },
              },
              {
                text: t(appName, 'About'),
                class: 'about',
                title: t(appName, 'Opens a window which tells you what this is.'),
                click() {
                  // maybe place this in a new template for greater flexibility.
                  OC.dialogs.message(
                    '<div class="bavhelp">'
                      + t(appName, 'This is a mere front-end to')
                      + '</div>'
                      + '<div class="bavhelp">'
                      + '<a target="_blank" href="http://bav.malkusch.de">'
                      + 'BAV -- The German Bank Account Validator</a>, &copy; M. Malkusch'
                      + '</div>'
                      + '<div class="bavhelp">'
                      + 'Owncloud front-end: &copy; C.-J. Heine'
                      + '</div>'
                      + '<div class="bavhelp">'
                      + t(appName, 'Data entered will not be stored. BAV is able to compute '
                          + 'the BIC given the bank-id, and to compute the IBAN '
                          + 'given additionally the bank-account id. '
                          + 'Additionally, it verifies that the bank-id exists '
                          + 'by comparing with the data provided by the Deutsche Bundesbank. '
                          + 'It will also pick the correct check-sum formula for the given '
                          + 'bank-account id (there are a few hundret possible '
                          + 'check-sum formulas, differing from bank to bank). '
                          + 'Finally, given an IBAN, it will extract the bank-id and bank-account-id '
                          + 'and also perform the check-sum computations in order to test whether the '
                          + 'provided bank-account data is consistent. Please keep in mind that check-sums '
                          + 'cannot catch all possible errors.'),
                    t(appName, 'About BAV'), 'info', OC.dialogs.OK_BUTTON, undefined, false, true);
                },
              },
            ],
            open() {
              const dialogHolder = $('#bav-container');
              const dialogWidget = dialogHolder.dialog('widget');

              dialogWidget.draggable('option', 'containment', '#content');

              if (modal) {
                $('#appmenu').prop('disabled', true);
              }

              // TODO: no more tipsy available
              dialogWidget.find('button').tooltip({ gravity: 'nw', fade: true, });
              dialogHolder.find('input').tooltip({ gravity: 'nw', fade: true, });

              dialogHolder.find('input[type="text"]')
                .off('blur')
                .on('blur', function(event) {
                  // alert('blur');
                  if (clearing) {
                    clearing = false;
                    return false;
                  }
                  event.stopImmediatePropagation();
                  validating = true;
                  $.post(OC.generateUrl('/apps/bav/validate'), dialogHolder.find('form').serialize())
                    .done(function(result) {
                      validating = false;
                      if (clearing) {
                        clearing = false;
                        return false;
                      }
                      // alert('RESULT '+result.bankAccountIBAN);
                      dialogHolder.find('input.bankAccountBIC').val(result.bankAccountBIC);
                      dialogHolder.find('input.bankAccountIBAN').val(result.bankAccountIBAN);
                      dialogHolder.find('input.bankAccountBankId').val(result.bankAccountBankId);
                      dialogHolder.find('input.bankAccountId').val(result.bankAccountId);
                      dialogHolder.find('input.bankAccountBankName').val(result.bankAccountBankName);
                      if (result.message == '') {
                        result.message = '&nbsp;';
                      }
                      dialogHolder.find('div.bav-status').html(result.message);
                      return false;
                    });
                  return false;
                });
            },
            close(event) {
              $('.tooltip').remove();
              dialogHolder.dialog('destroy');
              dialogHolder.remove();
              $('#appmenu').prop('disabled', false);
            },
          });
        });
    });
    return false;
  });

});

// Local Variables: ***
// js-indent-level: 2 ***
// indent-tabs-mode: nil ***
// End: ***
