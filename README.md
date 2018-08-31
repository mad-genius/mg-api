# MG API

An extendable PHP class for WordPress that provides methods for working with APIs. It's essentially a wrapper around `wp_remote_get` and `wp_remote_post`.

## Usage

Let's say we have an API `https://api.example.com/` and an API key `123456789`. We have the following endpoint:

`GET /movies` - returns a list of all movies. We can pass a `count` parameter to specify how many movies we want.

Using MG API, we can query the API like this:

```php
// create an instance and pass the base url
$example = new MG_API( 'https://api.example.com' );
