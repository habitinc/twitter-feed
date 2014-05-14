twitter-feed
============

Makes it easy to embed any public twitter timeline in a site

Simply use the function `fetch_tweets` to get all the recent tweets from a given handle. If you want to ensure you're getting a fresh batch of results, there is a second parameter to skip caching. Beware, this may affect the loading time of your site.

###### Examples

```php
// fetch the most recent tweets from twitter
$tweets = fetch_tweets('twitter');

//fetch the most recent tweets from twitter, ignoring caching
$tweets = fetch_tweets('twitter', 'ignore_cache');
```
