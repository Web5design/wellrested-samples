WellRESTed-Samples
==================

Sample files for demonstrating WellRESTed

### Installation

Clone this project onto a server under a website document root.

Download [Composer](http://getcomposer.org/) and use it to install the dependencies (WellRESTed).

```bash
$ curl -s https://getcomposer.org/installer | php
$ php composer.phar install
```

For the mini API in the **api** directory, you may need to modify some paths to fit your installation directory. Also, set the permissions to allow write access for the file **/api/lib/ApiSample/data/articles.json** if you would like to use the POST, PUT, and DELETE methods to modify the data.


Single-file Examples
--------------------

The **scripts** directory contains several stand-alone examples:



### server-side-response.php

Demonstrates how to create and output an HTTP response, complete with status code, headers an message body.


### client-side-request.php

Shows how to create a new request and communicate with another server. Specifically, it makes a simple GET request to Google.com and displayes the response.

WellRESTed uses [PHP's cURL](http://php.net/manual/en/book.curl.php) to make HTTP requsts, to ensure that you have this installed if you wish to use this feature.


### server-side-request-and-response.php

Here we read the request sent to the server and create a response based on the request. The script simply echoes information about the request. The response body contains JSON describing the method, body, and headers used in the original request.


API Sample
----------

The **api** directory contains a mini API project that demonstrates the main features of WellRESTEd. The following sections will show you how to use the API, and then how the API works under the hood.


### API: How to Use

For this sample project, the only resources are "articles", which are kind of like little mini blog posts or news feed items. Each article contains the following fields:

- **articleId:** Numeric unique identifier for the article
- **slug:** A human readable unique identifier for the article
- **title:** Text title describing the article
- **excerpt:** A short portion of the article's content

In JSON, an article resource looks like this:

```json
{
    "articleId": 1,
    "slug": "good-movie",
    "title": "Reports Of Movie Being Good Reach Area Man",
    "excerpt": "Local resident Daniel Paxson has reportedly heard dozens of accounts from numerous friendly sources in the past two weeks confirming that the new James Bond film is pretty good. According to persons with knowledge of the situation, an unnamed friend of Paxson’s coworker Wendy Mathers watched the movie on opening weekend and found it to be “decent enough.”"
}
```


#### URIs

The API exposes both the collection of articles and each article individually.

**/articles/**

Represents the collection of articles.

- **GET** Display the full list of articles.
- **POST** Add a new article. Provide the new article in JSON format as the
        request body.
- **PUT** Not allowed
- **DELETE** Not allowed


**/articles/{id}**
**/articles/{slug}**

Represents one specific article identified by the numberic ID {id} or by the alpha-numeric slug {slug}.

- **GET** Display one specific article.
- **POST** Not allowed
- **PUT** Replace the article with the new article. Provide the new article in
    JSON format as the request body.
- **DELETE** Remove the article.


### The API Explained

Here is a brief summary of the files with descriptions of their roles:


#### /.htaccess

The **.htaccess** file uses mod_rewrite to direct all incoming requests to non-regular files to the main **index.php** file.


#### /index.php

**index.php** requires the Composer autoload file, instantiates a Router, uses the Router to find the appropriate Handler class to build the response, and finally outputs the response.


#### /lib/ApiSample/ApiSampleRouter.php

The Router builds a list of rules (called Routes) that determine which Handler class it should defer to based on the request's URI. For example, examine this line from the contructor:

```php
$this->addTemplate('/articles/', 'ArticleCollectionHandler');
```

This line adds a new Route to the router instructing the Router to look for any request with the URI /articles/. When it receive a request to this URI, it should instantiate an ArticleCollectionHandler instance to deal with the request and issue a response.

The URIs for the routes can also include variables identified by regular expressions. Take a look at the next line in the ApiSampleRouter's constructor:

```php
$this->addTemplate('/articles/{id}', 'ArticleItemHandler', array('id' => Route::RE_NUM));
```

Here, we're using a URI templte to indicate that a variable must follow /articles/. The second parameter says that any requests matching this should be handled by an ArticleItemHandler instance. The third parameter describes the variables contained in the URI template. This parameter is an array of associative arrays with the keys as variables names from the template and the values as regular expressions that the variables must match. Route::RE_NUM is a predefined constant for digits only, but you can create your own, or use other predefined constants.

For more information on URI templates, see [RFC 6570](http://tools.ietf.org/html/rfc6570).


#### /lib/ApiSample/Handlers/ArticleCollectionHandler.php

This Handler is used whenever the Router receives a request for the URI /articles/. I've called it a "collection" handler because the resource it deals with are lists of articles rather than single articles. This handler allows you to view a list of articles (GET) or add a new article to the list (POST).


#### /lib/ApiSample/Handlers/ArticleItemHandler.php

The router invokes this Handler whenever it receives a request relating to a single article (hence "item" handler). For example, when you view a specific article (GET), update an existing article (PUT), or delete an article (DELETE).

#### /lib/ApiSample/ArticleController.php

The ArticleController class takes care of reading from and writing to the data file used to store the articles. This is a really simple example in which it just stores a flat JSON file. Your controllers could communicate with a database, cache, etc.

#### /lib/ApiSample/data/articles.json

Just a flat file storing some data in JSON format. You'll need to enable write access for this file to try out the POST, PUT, and DELETE methods of the API.
