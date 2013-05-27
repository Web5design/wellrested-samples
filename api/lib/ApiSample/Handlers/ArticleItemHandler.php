<?php

namespace ApiSample\Handlers;

use pjdietz\WellRESTed\Handler;
use ApiSample\ArticlesController;

/**
 * Handler class for one specific article.
 *
 * When instantiated by the Router, this class should receive an id or slug
 * argument to identify the article.
 */
class ArticleItemHandler extends Handler
{
    protected function getAllowedMethods()
    {
        return array('GET', 'PUT', 'DELETE');
    }

    /**
     * Respond to a GET request.
     */
    protected function get()
    {
        // Read the list of articles.
        $controller = new ArticlesController();

        // Locate the article by ID or slug
        $article = false;
        if (isset($controller->data)) {
            if (isset($this->args['id'])) {
                $article = $controller->getArticleById($this->args['id']);
            } elseif (isset($this->args['slug'])) {
                $article = $controller->getArticleBySlug($this->args['slug']);
            }
        }

        if ($article !== false) {
            $this->response->setStatusCode(200);
            $this->response->setHeader('Content-type', 'application/json');
            $this->response->setBody(json_encode($article));
        } else {
            $this->response->setStatusCode(404);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Unable to locate the article.');
        }
    }

    /**
     * Respond to a PUT request.
     */
    protected function put()
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

        // Ensure required fields are present.
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

        // Read the list of articles.
        $controller = new ArticlesController();

        // Locate the article by ID or slug
        $oldArticle = false;
        if (isset($controller->data)) {
            if (isset($this->args['id'])) {
                $oldArticle = $controller->getArticleById($this->args['id']);
            } elseif (isset($this->args['slug'])) {
                $oldArticle = $controller->getArticleBySlug($this->args['slug']);
            }
        }

        // Fail if the article identified by the URI does not exist.
        if ($oldArticle === false) {
            $this->response->setStatusCode(404);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Unable to locate the article.');
            return;
        }

        // If the user located the resource by ID and has passed a slug,
        // make sure the new slug is not already in use.
        if (isset($this->args['id'])) {
            $slugArticle = $controller->getArticleBySlug($article['slug']);
            if ($slugArticle && $slugArticle['articleId'] != $article['articleId']) {
                $this->response->setStatusCode(409);
                $this->response->setHeader('Content-type', 'text/plain');
                $this->response->setBody('Unable to store article. Slug "' . $article['slug'] . '" is already in use.');
                return;
            }
        }

        // Update the article.

        // First, ensure the articleId is set.
        // It must match the existing article found earlier.
        $article['articleId'] = $oldArticle['articleId'];

        // Keep the results from the update for the response.
        $article = $controller->updateArticle($article);

        if ($controller->save() === false) {
            $this->response->setStatusCode(500);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Unable to write to file. Make sure permissions are set properly.');
            return;
        }

        // Ok!
        $this->response->setStatusCode(200);
        $this->response->setHeader('Content-type', 'application/json');
        $this->response->setBody(json_encode($article));
        return;

    }

    /**
     * Respond to a DELETE request.
     */
    protected function delete()
    {
        // Read the list of articles.
        $controller = new ArticlesController();

        // Locate the article by ID or slug
        $article = false;
        if (isset($controller->data)) {
            if (isset($this->args['id'])) {
                $article = $controller->getArticleById($this->args['id']);
            } elseif (isset($this->args['slug'])) {
                $article = $controller->getArticleBySlug($this->args['slug']);
            }
        }

        // Ensure the article exists.
        if ($article === false) {
            $this->response->setStatusCode(404);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Unable to locate the article.');
            return;
        }

        // Remove the article and save.
        $controller->removeArticle($article['articleId']);

        if ($controller->save() === false) {
            $this->response->setStatusCode(500);
            $this->response->setHeader('Content-type', 'text/plain');
            $this->response->setBody('Unable to write to file. Make sure permissions are set properly.');
            return;
        }

        // Ok!
        $this->response->setStatusCode(200);
        return;

    }

}
