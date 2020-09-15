/**
 * ownCloud - bav
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Claus-Justus Heine 2014
 */

(function ($, OC) {

  $(document).ready(function () {

    var anchor = $('#appmenu li[data-id="bav"] a');
    anchor.click(function(event) {
console.log(OC.generateUrl('/apps/bav/bav'));
      $.post(OC.generateUrl('/apps/bav/bav'), {}).success(function(result) {
        var dialogHolder = $('<div id="bav-container"></div>');
        dialogHolder.html(result);

        var clearing = false;
        var validating = false;
        dialogHolder.dialog({
          title: t('bav', 'BAV - Bank Account Validator (DE)'),
          position:{my:'center center',
                    at:'center-5% center-10%',
                    of:window},
          width: 'auto',
          height: 'auto',
          modal: true,
          closeOnEscape: false,
          dialogClass: 'bav',
          resizable: false,
          buttons: [
            { 'text': t('bav', 'Clear'),
              'class': 'clear',
              'title': t('bav', 'Clear all fields of the input form'),
              click: function() {
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
              }
            },
            { 'text': t('bav', 'About'),
              'class': 'about',
              'title': t('bav', 'Opens a window which tells you what this is.'),
              'click': function() {
                // maybe place this in a new template for greater flexibility.
                OC.dialogs.info( '<div class="bavhelp">'
                               + t('bav', 'This is a mere front-end to')
                               + '</div>'
                               + '<div class="bavhelp">'
                               + '<a target="_blank" href="http://bav.malkusch.de">'
                               + 'BAV -- The German Bank Account Validator</a>, &copy; M. Malkusch'
                               + '</div>'
                               + '<div class="bavhelp">'
                               + 'Owncloud front-end: &copy; C.-J. Heine'
                               + '</div>'
                               + '<div class="bavhelp">'
                               + t('bav', 'Data entered will not be stored. BAV is able to compute '
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
                                t('bav', 'About BAV'), undefined, false, true);
              }
            }
          ],
          open: function() {
            var dialogHolder = $('#bav-container');
            var dialogWidget = dialogHolder.dialog('widget');

            dialogWidget.draggable('option', 'containment', '#content');

            $('#navigation').hide();

            // TODO: no more tipsy available
            dialogWidget.find('button').tipsy({gravity:'nw', fade:true});
            dialogHolder.find('input').tipsy({gravity:'nw', fade:true});

            dialogHolder.find('input[type="text"]').
              off('blur').
              on('blur', function(event) {
              //alert('blur');
              if (clearing) {
                clearing = false;
                return false;
              }
              event.stopImmediatePropagation();
              validating = true;
              $.post(OC.generateUrl('/apps/bav/validate'),
                     dialogHolder.find('form').serialize()).success(function(result) {
                validating = false;
                if (clearing) {
                  clearing = false;
                  return false;
                }
                //alert('RESULT '+result.bankAccountIBAN);
                dialogHolder.find('input.bankAccountBIC').val(result.bankAccountBIC);
                dialogHolder.find('input.bankAccountIBAN').val(result.bankAccountIBAN);
                dialogHolder.find('input.bankAccountBankId').val(result.bankAccountBankId);
                dialogHolder.find('input.bankAccountId').val(result.bankAccountId);
                dialogHolder.find('input.bankAccountBankName').val(result.bankAccountBankName);
                dialogHolder.find('div.bav-status').html(result.message);
                return false;
              });
              return false;
            });
          },
          close: function(event) {
            $('.tipsy').remove(); // remove stray tooltips
            $('.tooltip').remove();
            dialogHolder.dialog('destroy');
            dialogHolder.remove();
          }
        });
      });
      return false;
    });

  });

})(jQuery, OC);
