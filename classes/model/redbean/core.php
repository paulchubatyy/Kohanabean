<?php
/**
 * Application models may extends this class
 * @package		Kohanabean
 * @category	Model
 */
abstract class Model_RedBean_Core {
	/**
	 * Field filters. Key is field name => value is array of filters to apply
	 * @var	array
	 */
	protected $filters = array();
	/**
	 * Default filter applied to all model fields
	 * @var	string
	 */
	protected $default_filter = 'trim';
	/**
	 * Validation rules applied to the model fields
	 * Key is field name => value is array of rules to apply
	 * @see guide/api/Validation
	 * @var	array
	 */
	protected $rules = array();
	/**
	 * Validation object
	 * @var	Validation
	 */
	protected $validation;
	/**
	 * Default columns of the model
	 */
	protected $columns = array();
	/**
	 * Contains the inner bean.
	 * @var RedBean_OODBBean
	 */
	protected $bean;

	public function __construct($data = NULL)
	{
		$this->r = Rb::i();
		if ($data == NULL) {
			$this->r->dispense($this->get_model_name());
		} elseif (is_array($data)) {
			$this->r->dispense($this->get_model_name());
			$this->set_data($data);
		} else {
			$this->r->load($this->get_model_name(), $data);
		}

	}

	/**
	 * Used by FUSE: the ModelHelper class to connect a bean to a model.
	 * This method loads a bean in the model.
	 * @param RedBean_OODBBean $bean
	 */
	public function loadBean( RedBean_OODBBean $bean ) {
		$this->bean = $bean;
	}

	/**
	 * Magic Getter to make the bean properties available from
	 * the $this-scope.
	 * @param string $prop
	 * @return mixed $propertyValue
	 */
	public function __get( $prop )
	{
		return $this->bean->$prop;
	}

	/**
	 * Filters the value before setting it according to the model filters
	 * @param	string	property name
	 * @param	mixed	protecty value
	 * @return	void
	 */
	public function __set($key, $value)
	{
		$filters = Arr::get($this->filters, $key, array());
		foreach ($filters as $filter) {
			$value = call_user_func($filter, $value);
		}
		if ($default_filter != NULL) {
			$value = call_user_func($this->default_filter, $value);
		}
		$this->bean->$prop = $value;
	}

	protected function __hasProperties($list)
	{
		$missing = array();
		$properties = explode(",", $list);
		foreach($properties as $property) {
			if (empty($this->bean->$property)) {
				$missing[] = $property;
			}
		}
		return $missing;
	}

	protected $model_name;
	protected function get_model_name()
	{
		if ($this->model_name == NULL) {
			// Argument is mandatory, @see http://php.net/get_class
			$class = get_class($this);
			$class = explode('_', $class);
			array_shift($class);
			$this->model_name = implode('_', $class);
		}
		return $this->model_name;
	}

	/**
	 * Validates the model
	 * @return	true
	 */
	public function validate()
	{
		$this->validation = Validation::factory($this->as_array())
			->bind(':model', $this);
		foreach ($this->rules as $key => $rules) {
			$validation->rules($key, $rules);
		}
		return $this->validation->check();
	}

	/**
	 * Loads the data into the model. If second parameter is true loads all
	 * the values provided, if null loads default columns from model, if array
	 * loads the provided columns.
	 * @param	array 	Data to load
	 * @param	mixed
	 * return	object	$this
	 */
	public function set_data($data, $columns = NULL)
	{
		if ($columns == NULL) {
			$columns = $this->columns;
		}
		if (is_array($columns) AND ! empty($columns)) {
			$data = Arr::extract($data, $columns);
		}
		foreach ($data as $key => $value) {
			$this->{$key} = $value;
		}
		return $this;
	}

	/**
	 * Returns the array representation of the model
	 * @return	array
	 */
	public function as_array()
	{
		return $this->bean->export();
	}

	/**
	 * Create the instance of the model
	 * @param	array 	Data to create from
	 * @return	object	$this
	 * @throws	Validation_Exception
	 */
	public function create($data)
	{
		$this->set_data($data);

		if ( ! $this->validate()) {
			throw new Validation_Exception(
				$this->validation, 'Failed to validate RedBean model');
		}
		$this->r->store($this->bean);
		return $this;
	}

	/**
	 * Modify the instance of the model. If second parameter is passed the model
	 * is loaded from the database, updated and saved back.
	 * @param	array	Data to modify
	 * @param	mixed	UUID of the entry
	 * @return	object	$this
	 * @throws	Validation_Exception
	 */
	public function modify($data, $id = NULL)
	{
		if ($id != NULL) {
			$this->r->load($this->get_model_name(), $id);
		}
		$this->set_data($data);
		if ( ! $this->validate()) {
			throw new Validation_Exception(
				$this->validation, 'Failed to vaildate RedBean model');
		}
		$this->r->store($this->bean);
		return $this;
	}

	public function remove($id = NULL)
	{
		if ($id != NULL) {
			$this->r->load($this->get_model_name(), $id);
		}
		$this->r->trash($this->bean);
	}
}
