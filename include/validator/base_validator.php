<?php 


class base_validator
{
    private $errors = [];

	public function isValid(): bool
	{
		return empty($this->errors);
	}

	public function getErrors(): array
	{
        $errors = $this->errors;
        $this->errors = [];
		return $errors;
	}

    public function setError(string $field, string $message)
	{
		$this->errors[] = ["field" => $field, 'message' => $message];
	}
}