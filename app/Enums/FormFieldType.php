<?php

namespace App\Enums;

enum FormFieldType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EMAIL = 'email';
    case TEL = 'tel';
    case NUMBER = 'number';
    case DATE = 'date';
    case SELECT = 'select';
    case MULTISELECT = 'multiselect';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';
    case FILE = 'file';
    case HEADING = 'heading';
    case PARAGRAPH = 'paragraph';
}
