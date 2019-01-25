# MG API

An extendable PHP class for WordPress that provides methods for working with APIs. It’s essentially a wrapper around `wp_remote_get` and `wp_remote_post`.

## Reference

### Properties

#### $api_base_url
The base URL (not including endpoint path). Ex: _<https://api.example.com>_.

```php
public string $api_base_url;
```

### Methods

#### __construct
Returns the `MG_API` instance.

```php
public __construct([ string $base_url = '' [, string $hash = '' ]]);
```

#### endpoint
Returns a full URL consisting of the base URL and the endpoint path.

```php
public endpoint( string $path = NULL );
```

#### get
Returns the response of [`wp_remote_get`](https://developer.wordpress.org/reference/functions/wp_remote_get/).

```php
public get( string $url [, array $params = array() ]);
```

#### post
`$args` gets passed directly to [`wp_remote_post`](https://developer.wordpress.org/reference/functions/wp_remote_post/). Returns the response of [`wp_remote_post`](https://developer.wordpress.org/reference/functions/wp_remote_post/).

```php
public post( string $url [, array $params = array() [, array $args = array() ]]);
```

## Usage

Let's say we have an API `https://api.example.com/` and an API key `123456789`. We have the following endpoint:

`GET /movies` - returns a list of all movies. We can pass a `count` parameter to specify how many movies we want.

Using MG API, we can query the API like this:

```php
// create an instance and pass the base url
$api = new MG_API( 'https://api.example.com/' );

// create a url of the appropriate endpoint
$movies_url = $api->endpoint( '/movies' );

// send a GET request with a query string
$movies_response = $api->get( '$movies_url, array(
    'api_key' => 123456789,
    'count' => 10
) );
```

The `get` method takes URL path as a string and a list of parameters as an array, which it converts into a query string. It passes the resulting URL to [`wp_remote_get`](https://developer.wordpress.org/reference/functions/wp_remote_get/) and returns the result.

Let’s say that we want to post a review at an endpoint, `/reviews`. It takes the `api_key` (integer), a `movie_id` (integer), and a `review` (string). The body of the request should be a JSON string. Assuming our first request returned a JSON response, we could do something like this:

```php
// get the list of movies from our last query
$movies = json_decode( $movies_response['body'], true );

// find the movie we want to review
$matrix = array_filter( $movies, function( $movie ) {
    return $movie['title'] === 'The Matrix' );
} )[0];

// create the endpoint URL
$review_url = $api->endpoint( '/reviews' );

// issue a POST request
$reviews_response = $api->post( $review_url, array(
    'api_key' => 123456789,
    'movie_id' => $matrix['id'],
    'review' => 'There is no spoon.'
) );
```

By default, MG API assumes you want to post a JSON string. That means all we do is pass a PHP array—MG API will encode it for us as well as set the appropriate `Content-Type` header. The return value is the response of [`wp_remote_post`](https://developer.wordpress.org/reference/functions/wp_remote_post/).

### Extending `MG_API`

The above example is fine, but what would be better is if we were to make a `Movies` class that would make these request patterns reusable.

```php
class Movies extends MG_API {

    private $api_key;

    public function __construct( $api_base_url, $api_key ) {
        // save the API key
        $this->api_key = $api_key;
        
        // call MG_API’s constructor
        parent::__construct( $api_base_url );
    }
    
    public function get_movies( $count = 10 ) {
        // get the endpoint URL
        $url = $this->endpoint( '/movies' );
        
        // send a GET request with a query string
        $response = $this->get( $url, array(
            'api_key' => $this->api_key,
            'count' => $count
        ) );
        
        // return a PHP array
        return json_decode( $response['body'], true );
    }
    
    public function get_movie( $title ) {
        // get a list of movies
        $movies = $this->get_movies();
        
        // return the movie we want
        return array_filter( $movies, function( $movie ) use ( $title ) {
            return $movie['title'] === $title;
        } )[0];
    }
    
    public function add_review( $review, $movie_id ) {
        // get the endpoint URL
        $url = $this->endpoint( '/reviews' );
        
        // send a POST request
        $response = $this->post( $url, array(
            'api_key' => $this->api_key,
            'movie_id' => $movie_id,
            'review' => $review
        ) );
        
        // return a PHP array
        return json_decode( $response['body'], true );
    }
    
}
```

Now we can perform our requests much easier than before. We can do it in three lines of code:

```php
// create an instance of our class
$movie_api = new Movies( 'https://api.example.com/' );

// get the movie we want
$matrix = $movie_api->get_movie( 'The Matrix' );

// add our review
$movie_api->add_review( 'There is no spoon.', $movie['id'] );
```

### Disclaimer

The above example code is untested. Please consider it pseudo-code. If you see a mistake, please create an issue or make a pull request.
