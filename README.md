# aeza-api
Private Aeza API Service
## About the project
It is a web service project to manage the aeza website

## Use - For Example:  
```php
// Get Profile
(new Service('c802375027b448787792b3ac29426f04'))->profileGet();

// Product list
(new Service('c802375027b448787792b3ac29426f04'))->products();

// Creating server
 (new Service('89dbab6e53b8827de26a9e6789607e4e'))->createServer([
"productId"=> 3,
"term"=>'hour',
"autoProlong"=> "false",
"name"=> 'test-name',
  ]);

```
