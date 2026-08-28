<?php

namespace App\Domains\Channels\WebForm\Models;

enum WebFormFieldType: string
{
    case Text = 'text';
    case TextArea = 'textarea';
    case Email = 'email';
    case Phone = 'phone';
    case Number = 'number';
    case Date = 'date';
    case Select = 'select';
    case MultiSelect = 'multiselect';
    case Boolean = 'boolean';
}
