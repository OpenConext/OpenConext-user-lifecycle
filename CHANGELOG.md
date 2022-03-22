## 0.1.0
This release is mainly focussed on making LifeCycle more robust in multiple areas.

**Maintenance:** 
- Drop PHP 5.6 version compatibility #40
- Upgrade to Symfony 4.4 (from version 3.4) #42
- Bring Monolog config in sync with OC projects #53

**New features:**
- Migrate TravisCI to Github Actions for test-integration #50
- Improve status reporting #51
- Improve CollabPersonId input validation #43 #48

**Other chores**
- Replace the Vagrant dev-env with Docker #41
- Update documentation #44 #45
- Create a last_login test data seeder #52

## 0.0.5
 - Add sensio distribution bundle to normal dependency instead dev dependency

## 0.0.4
 - Upgraded Symfony to version 3.4.27
 - Configured the health endpoints to become publicly available

## 0.0.3
 - Upgraded Symfony to version 3.4.14

## 0.0.2
 - The information retrieval feature from the deprovision command is now exposed through a web API. See readme for more details. 

## 0.0.1
 - The deprovision API's can be configured in `app/config/parameters.yml`. For more information see the README.md and `parameters.yml.dist` files.
 - The `information` console action was added enabling the retrieval of user information.
