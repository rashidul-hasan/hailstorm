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

        $fieldsCollection = new FieldsCollection($this->fields);

        // action buttons
        $buttons = $this->crudAction->renderIndexActions();

        $this->viewData = [
            'title' => $this->model->getEntityNamePlural(),
            'model' => $this->model,
            'buttons' => $buttons,
            'view' => $this->indexView,
            'dataRoute' => $this->getRoute('index'),
            'routePrefix' => $this->routePrefix,
            'entityName' => Str::singular($this->getEntityName()),
            'dtActions' => $this->dtActions,
            'includeView' => $this->includeView['index'] ?? null,
            'indexFields' => $fieldsCollection->getIndexFields()
        ];

        if ($this->crudType === Constants::CRUDTYPE_MODAL) {
            $form = FormBuilder::build($fieldsCollection->getFormFields());
            $this->viewData['title'] = Str::plural($this->getEntityName());
            $this->viewData['form'] = $form;
            $this->viewData['view'] = 'hailstorm::crud-modal.index';
            $this->viewData['formFields'] = $fieldsCollection->getFormFields();
        }

        if ($this->crudType === Constants::CRUDTYPE_SINGLEPAGE) {
            $form = FormBuilder::build($fieldsCollection->getFormFields());
            $this->viewData['title'] = Str::plural($this->getEntityName());
            $this->viewData['form'] = $form;
            $this->viewData['view'] = 'hailstorm::crud-singlepage.index';
            $this->viewData['formFields'] = $fieldsCollection->getFormFields();
        }
        $this->callHookMethod('indexing');

        return $this->responseBuilder->send($this->request, $this->viewData);

    }

}
