## 0.1.2
And here for some final optimizations to get userlifecycle work as intended on a production environment. The final 
hurdles where to tweak the release script:
 - Set environments to prod and remove cache folders,
 - Remove the prod cache folder from the tarbal.

And finally, dependabot upgraded the guzzlehttp/psr7 package. 

## 0.1.1
Two missing features where added to 0.1.0. They are:

- The exit code changed to 1 when one of the deprovision actions failed. Ensuring the output will be mailed by the 
  cron mailer to the sysadmin.  #55
- The deprovision stats are now logged to syslog, before they only ended up in the console output.  #55

And as an added bonus, the documentation was updated to be more specific on the JSON format the UserLifecycle 
application expects from the clients

- Improve UserLifecycle documentation #54

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
