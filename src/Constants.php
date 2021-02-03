<?php


namespace Rashidul\Hailstorm;


class Constants
{
    const CRUDTYPE_BASIC = 1;
    const CRUDTYPE_MODAL = 2;
    const CRUDTYPE_SINGLEPAGE = 3;

    // field types
    const TYPE_TEXT = 'text';
    const TYPE_EMAIL = 'email';
    const TYPE_PASSWORD = 'password';
    const TYPE_SELECT = 'select';
    const TYPE_SELECT_DB = 'select_db';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO = 'radio';

    // datatable actions
    const DATATABLE_ACTION_VIEW = 'DATATABLE_ACTION_VIEW';
    const DATATABLE_ACTION_EDIT = 'DATATABLE_ACTION_EDIT';
    const DATATABLE_ACTION_DELETE = 'DATATABLE_ACTION_DELETE';
}
