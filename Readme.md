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