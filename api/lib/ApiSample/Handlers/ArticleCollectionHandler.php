<?php

namespace ApiSample\Handlers;

use pjdietz\WellRESTed\Handler;
use ApiSample\ArticlesController;

/**
 * Handler class for a list of articles.
 */
class ArticleCollectionHandler extends Handler
{
    protected function getAllowedMethods()
    {
        return array('GET', 'POST');
    }

    /**
     * Respond to a GET request.
     */
    protected function get()
    {
        // Display the list of articles.
        $controller = new ArticlesController();

        if (isset($controller->data)) {
            $this->response->setStatusCode(200);
            $this->response->setHeader('Content-type', 'application/json');
            $this->response->setBody(json_encode($controller->data));
        } else {
            $this->response->setStatusCode(500);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Unable to read the articles.');
        }
    }

    /**
     * Respond to a POST request.
     */
    protected function post()
    {
        // Read the request body, and ensure it is in the proper format.
        $article = json_decode($this->request->getBody(), true);

        // Ensure the JSON is well-formed.
        if (!$article) {
            $this->response->setStatusCode(400);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Unable to parse JSON from request body.');
            return;
        }

        // Ensure requied fields are present.
        if (!isset($article['slug']) || $article['slug'] === '') {
            $this->response->setStatusCode(400);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Request body missing slug.');
            return;
        }

        if (!isset($article['title'])) {
            $this->response->setStatusCode(400);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Request body missing title.');
            return;
        }

        if (!isset($article['excerpt'])) {
            $this->response->setStatusCode(400);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Request body missing excerpt.');
            return;
        }

        // Ensure slug is not a duplicate.
        $articles = new ArticlesController();
        if ($articles->getArticleBySlug($article['slug']) !== false) {
            $this->response->setStatusCode(409);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Unable to store article. Slug "' . $article['slug'] . '" is already in use.');
            return;
        }

        // All looks good! Add this to the articles and save!
        $article = $articles->addArticle($article);

        if ($articles->save() === false) {
            $this->response->setStatusCode(500);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Unable to write to file. Make sure permissions are set properly.');
            return;
        }

        // Ok!
        $this->response->setStatusCode(201);
        $this->response->setHeader('Content-type', 'application/json');
        $this->response->setBody(json_encode($article));
        return;

    }

}
