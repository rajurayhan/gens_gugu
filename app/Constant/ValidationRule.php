<?php

namespace App\Constant;


class ValidationRule
{
    public const COLUMN_NAME = ['string', 'regex:/([0-9a-zA-Z$_])+/'];
    public const SORT_ORDER = ['regex:/(^asc$|^desc$|^ASC$|^DESC$)/'];
}
