# BAV -- German Bank Account Validator

<!-- markdown-toc start - Don't edit this section. Run M-x markdown-toc-refresh-toc -->
**Table of Contents**

- [Info](#info)
- [Disclaimer](#disclaimer)
- [Privacy and Security](#privacy-and-security)
- [Testing](#testing)
- [Screenshots](#screenshots)

<!-- markdown-toc end -->

## Info

This is a frontend to the bank-account validator written by
M. Malkusch, see [BAV Github
project](https://github.com/bav-php/bav). The actuall backend used,
however, is [my fork](https://github.com/rotdrop/bav.git) as
unfortunately the original Github repo is no longer maintained, it
seem.

BAV validates IBAN checksums by the known methods (using
[php-iban](https://github.com/globalcitizen/php-iban)) and using the
[BAV backend](https://github.com/bav-php/bav) it also validates the
contained bank-id and bank-account-id. The Deutsche Bundesbank
maintains a registry of banks and a registry of checksum algorithm for
validating a bank account id. The data-sets are publically available
and scraped from the web-pages of the Deutsche Bundesbank on a regular
bases and copied to the Nextcloud database in tables `bav_agency`,
`bav_bank` and `bav_meta` (the latter just contains the timestamp of
the last fetch, so you could consult the table in order to check when
the last update happened).

The Nextcloud BAV frontend is based on Vue and just display a small
dialog where bank account information can be entered. The BAV-app will
verify a given IBAN, extract the bank id and bank account id and
verify that the bank exists and then use the registered checksum
algoritm to validate also the account id.

Vice-versa, when entering bank id and bank-account id it will generate
the proper IBAN.

## Disclaimer

Please make sure that you have read and understood the terms of the GNU
Affero General Public License (AGPL), in particular the paragraphs
§15, §16, §17 (AKA "no warranty"). In summary:

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

The software package also contains a [copy of the LGPL](./COPYING).

## Privacy and Security

- The bank data is submitted to the server running the Nextcloud
instance by POST request, that is it is secured by the server's SSL
encryption. If you use unsecure unencrypte HTTP connection
(i.e. http://... instead of https://..) then the data will not be
encrypted.

- The server then runs PHP scripts to validate and possibly complete
  the given (fractional) bank account data, however, it does not store
  those data.

- The web-frontend maintains an undo/redo stack, but this data is only
  stored in your web-browser. If you close the browser tab or window
  then also the undo-stack is gone.

## Testing

There are some German "dummy" IBANs which do not correspond to actual
bank accounts but pass the validation, e.g. `DE89 3704 0044 0532 0130 00`

## Screenshots

- [Dialog with above IBAN in German language](./contrib/screenshots/bav-dialog-german.png)
- [Dialog with above IBAN in English language](./contrib/screenshots/bav-dialog-german-english.png)
- [Dialog with only bank-id in English language](./contrib/screenshots/bav-dialog-bank-only-english.png)
