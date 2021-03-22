<?php

namespace App\Exceptions;

use Exception as BaseException;

class Exception extends BaseException
{

    protected $data = null;

    protected $errors = null;

    protected $statusCode = 200;

    public function __construct($message = '', $code = 0, $data = null, $errors = null, $statusCode = 200)
    {
        parent::__construct($message, $code);
        $this->data = $data;
        $this->errors = $errors;
        $this->statusCode = $statusCode;
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        return;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json([
            'code'    => $this->code,
            'data'    => $this->data,
            'errors'  => $this->errors,
            'message' => $this->message,
        ], $this->statusCode);
    }
}
