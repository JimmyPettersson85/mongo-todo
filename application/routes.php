<?php

/**
* Get all the todo documents from the database.
*
*/
Route::get('api/todos', function()
{
	$todos = TodoMongoWrapper::getAllTodos();
	return $todos;
});

/**
* Create a new todo document.
*/
Route::post('api/todos', function()
{
	if (Input::has('message'))
	{
		$message = htmlspecialchars(Input::get('message'));
		$done = Input::get('done', false);
		if ($done && $done === 'true') $done = true;
		$todo = TodoMongoWrapper::createTodo($message, $done);
		return $todo;
	}

	return TodoMongoWrapper::error('missing parameter message');
});

/**
* Get a single todo document identified by $id.
*/
Route::get('api/todos/(:any)', function($id)
{
	return TodoMongoWrapper::getTodo($id);
});


/**
* Update an existing todo document identified by $id.
*/
Route::put('api/todos/(:any)', function($id)
{
	if (Input::has('message'))
	{
		$message = htmlspecialchars(Input::get('message'));
		$done = Input::get('done', false);
		$done = ($done && $done === 'true') ? true : false;
		$todo = TodoMongoWrapper::updateTodo($id, $message, $done);
		return $todo;
	}

	return TodoMongoWrapper::error('missing parameter message');
});

/**
* Deletes a todo document identified by $id.
*/
Route::delete('api/todos/(:any)', function($id)
{
	return TodoMongoWrapper::deleteTodo($id);
});

/*
|--------------------------------------------------------------------------
| Application 404 & 500 Error Handlers
|--------------------------------------------------------------------------
|
| To centralize and simplify 404 handling, Laravel uses an awesome event
| system to retrieve the response. Feel free to modify this function to
| your tastes and the needs of your application.
|
| Similarly, we use an event to handle the display of 500 level errors
| within the application. These errors are fired when there is an
| uncaught exception thrown in the application.
|
*/

Event::listen('404', function()
{
	return TodoMongoWrapper::error('404');
});

Event::listen('500', function()
{
	return TodoMongoWrapper::error('500');
});

/*
|--------------------------------------------------------------------------
| Route Filters
|--------------------------------------------------------------------------
|
| Filters provide a convenient method for attaching functionality to your
| routes. The built-in before and after filters are called before and
| after every request to your application, and you may even create
| other filters that can be attached to individual routes.
|
| Let's walk through an example...
|
| First, define a filter:
|
|		Route::filter('filter', function()
|		{
|			return 'Filtered!';
|		});
|
| Next, attach the filter to a route:
|
|		Router::register('GET /', array('before' => 'filter', function()
|		{
|			return 'Hello World!';
|		}));
|
*/

Route::filter('before', function()
{
	// Do stuff before every request to your application...
});

Route::filter('after', function($response)
{
	// Do stuff after every request to your application...
});

Route::filter('csrf', function()
{
	if (Request::forged()) return Response::error('500');
});

Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::to('login');
});