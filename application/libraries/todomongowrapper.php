<?php

class TodoMongoWrapper {

	/**
	* Fetches all documents in the database.
	*
	* @return JSON string
	*/
	public static function getAllTodos()
	{
		$cursor = self::_call('findAll');
		$todos = array();
		$i = 0;
		while($cursor->hasNext())
		{
			$todos[$i] = $cursor->getNext();
			$todos[$i++]['_id'] = $cursor->key();
		}

		return json_encode($todos);
	}

	/**
	* Fetches a single document from the database.
	*
	* @param string $id
	* @return JSON string
	*/
	public static function getTodo($id)
	{
		$todo = self::_call('findOne', array('id' => $id));
		if ($todo)
		{
			return json_encode(array(
				'_id' => $id,
				'message' => $todo['message'],
				'done' => $todo['done'],
				'created_at' => $todo['created_at'],
				'updated_at' => $todo['updated_at']
			));
		}
		else
		{
			return self::error('id '.$id.' not found in database');
		}
	}

	/**
	* Creates a new todo document.
	*
	* @param string message
	* @param boolean done
	* @return JSON string
	*/
	public static function createTodo($message, $done)
	{
		if (! is_string($message) || ! strlen(trim($message)))
		{
			return self::error('parameter message needs to be a string with length > 0');
		}
		else if (! is_bool($done))
		{
			return self::error('parameter done needs to be a boolean, got '.$done);
		}
		else
		{
			$now = time();
			$todo = array(
				'message' => $message,
				'done' => $done,
				'created_at' => $now,
				'updated_at' => $now
			);

			self::_call('create', array('todo' => $todo));
			$todo['_id'] = $todo['_id']->{'$id'};

			return json_encode($todo);
		}
	}

	/**
	* Updates a todo document.
	*
	* @param string $id
	* @param string $message
	* @param boolean done
	* @return JSON string
	*/
	public static function updateTodo($id, $message, $done)
	{
		if (! is_string($message) || ! strlen(trim($message)))
		{
			return self::error('parameter message needs to be a string with length > 0');
		}
		else if (! is_bool($done))
		{
			return self::error('parameter done needs to be a boolean, got '.$done);
		}
		else
		{
			$todo = self::_call('update', array(
				'id' => $id,
				'message' => $message,
				'done' => $done
			));

			if ($todo)
			{
				return json_encode(array(
					'_id' => $id,
					'message' => $todo['message'],
					'done' => $todo['done'],
					'created_at' => $todo['created_at'],
					'updated_at' => $todo['updated_at']
				));
			}
			else
			{
				return self::error('id '.$id.' not found in database');
			}
		}		
	}

	/**
	* Deletes a todo document.
	*
	* @param string $id
	* @return JSON string
	*/
	public static function deleteTodo($id)
	{
		$removed = self::_call('delete', array('id' => $id));
		if ($removed)
		{
			return json_encode(array(
				'removed' => true
			));
		}
		else
		{
			return self::error('could not remove document with id '.$id);
		}
	}

	/**
	* Sets error and error message.
	*
	* @return JSON string
	*/
	public static function error($message)
	{
		return json_encode(array(
			'error' => true,
			'errorMessage' => $message
		));
	}


	/**
	* Handles all the interactions with the Mongo database.
	*
	* @param string $action
	* @param array $parameters
	* @return mixed
	*/
	private static function _call($action, array $parameters = array())
	{
		$instance = new Mongo();
		$collection = $instance->tododb->todos;

		switch ($action) {
			case 'findAll':
				return $collection->find();
			case 'findOne':
				return $collection->findOne(array("_id" => new MongoId($parameters['id'])));
			case 'create':
				$collection->insert($parameters['todo']);
				break;
			case 'update':
				$todo = $collection->findOne(array("_id" => new MongoId($parameters['id'])));
				if (! $todo) return false;
				$todo['message'] = $parameters['message'];
				$todo['done'] = $parameters['done'];
				$todo['updated_at'] = time();
				if (! $collection->save($todo)) return false;
				return $todo;
			case 'delete':
				return $collection->remove(array('_id' => new MongoId($parameters['id'])), array('justOne' => true));

			default:
				return false;
		}
	}
}