# Change Log (shopbay-api)

## Version 0.25 - Aug 5, 2018

This release contains several enhancements, as well as also supports PHP 7.2 and Yii 1.1.20 and Yii 2.0.15

### Enhancements:

 - Enh: Upgraded to support PHP 7.2
 - Enh: Upgraded to support Yii 1.1.20 and Yii 2.0.15
 - Chg: Make Codeception acceptance/functional/unit testing framework works again according to latest API implementation.
 - Chg: Added Codeception migration support to drop foreign keys of certain tables due to testing constraint of adding/deleting data
 - Chg: Support Shop and Subscription fixtures at codeception

### Bug fixes:

 - Bug: Added missing folders: runtime, web/assets


## Version 0.24 - Jun 24, 2017

This is the initial release of `shopbay-api`, part of Shopbay.org open source project. 

It includes code re-architecture and refactoring to separate the `api` app out from old code.
All existing functions and features remain same as inherited from previous code base (v0.23.2).

For full copyright and license information, please view the [LICENSE](LICENSE.md) file that was distributed with this source code.


## Version 0.23 and before - June 2013 to March 2017

Started since June 2013 as private development, the beta version (v0.1) was released at September 2015. 

Shopbay.org open source project was created by forking from beta release v0.23.2 (f4f4b25). 