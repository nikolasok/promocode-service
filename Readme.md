# Task

- It is necessary to implement a system for issuing promo codes
- It is necessary to generate 500k unique promo codes (it can be a one-time script)
- A promo code is a string of 10 characters
- One promo code can be issued only to one user
- One user cannot receive more than one promo code
- No more than 1000 promo codes can be issued from one IP address
- It is necessary to save the date of issue of each promo code

# Result

- Page with a form for issuing a promo code
- By clicking on the button, the user receives a promo code, and a redirect with the specified promo code to the partner's website occurs: https://www.google.com/?query=PROMOCODE
- A repeated click by the same user on the button leads to a redirect to the same page (with the promo code issued earlier)
- The functionality of issuing a promo code must be covered by tests

# Limitations

- Mysql 5.7+ database
- Promo codes in the table: 500,000
- PHP version 8+ (without using frameworks)
- You can use libraries
- You can't use javascript

### We will pay attention to:
- code
- query optimality
- work speed

### We will not pay attention to:
- css styles and layout

____
# Run

Run containers
````
docker compose up --build
````

Generate promocodes
````
make setup
````

Open in browser
````
http://localhost/
````

You can find the test report in ./reports/Service/PromoCode/PromoCodeService.php.html

Project structure
````
-bin : entry points for frankenphp server and script for uploading promocodes
-config
--bootstrap.php : bootstrap files
--routes.php : routes configuration
--services.yaml : DI configuration
-data : schema and session storage
-reports : phpunit test coverage report
-src
--Command : command for uploading promocodes
--Http : folder for controllers
--Infrastructure : core specific code
--Service : feature imlementation
-tests : phpunit tests
````

Dependencies in project:  
symfony/dependency-injection - for DI  
symfony/dotenv - for ENV loading  
nikic/fast-route - for route configuration  
ramsey/uuid - for device id generation
