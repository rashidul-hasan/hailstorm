<?php

namespace Rashidul\Hailstorm\Crud;


use Illuminate\Support\Str;
use Rashidul\Hailstorm\Constants;
use Rashidul\Hailstorm\Facades\DataTable;
use Rashidul\Hailstorm\Facades\FormBuilder;
use Rashidul\Hailstorm\FieldsCollection;
use Rashidul\Hailstorm\Table\Helper;
use Yajra\DataTables\Facades\DataTables;

trait Index
{

    /**
     * Display a listing of the Resources.
     * @return Response
     * @internal param Request $request
     */
    public function index()
    {

        // ajax request, send datatable reposnse
        if ($this->request->ajax()) {
            $this->dataTableQuery = $this->model->select();
            $this->dataTableObject = $this->dataTable->eloquent($this->dataTableQuery)
                ->rawColumns([]);
            $this->callHookMethod('querying');

            return $this->dataTableObject->make(true);
        }

        $this->crudAction->failIfNotPermitted('index');

        $table = DataTable::of(new $this->modelClass)
            ->setUrl($this->model->getDataUrl());

        // action buttons
        $buttons = $this->crudAction->renderIndexActions();

        $this->viewData = [
            'title' => $this->model->getEntityNamePlural(),
            'model' => $this->model,
            'table' => $table,
            'buttons' => $buttons,
            'view' => $this->indexView,
            'dataRoute' => $this->getRoute('index'),
            'routePrefix' => $this->routePrefix,
            'entityName' => Str::singular($this->getEntityName()),
            'dtActions' => $this->dtActions,
            'includeView' => $this->includeView['index'] ?? null,
        ];

        if ($this->crudType === Constants::CRUDTYPE_MODAL) {
            $fieldsCollection = new FieldsCollection($this->fields);
            $form = FormBuilder::build($fieldsCollection->getFormFields());
            $this->viewData['title'] = Str::plural($this->getEntityName());
            $this->viewData['form'] = $form;
            $this->viewData['view'] = 'hailstorm::crud-modal.index';
            $this->viewData['indexFields'] = $fieldsCollection->getIndexFields();
            $this->viewData['formFields'] = $fieldsCollection->getFormFields();
        }
        $this->callHookMethod('indexing');

        return $this->responseBuilder->send($this->request, $this->viewData);

    }

}
