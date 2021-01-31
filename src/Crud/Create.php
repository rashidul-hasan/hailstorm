<?php

namespace Rashidul\Hailstorm\Crud;


use Illuminate\Support\Str;
use Rashidul\Hailstorm\Constants;
use Rashidul\Hailstorm\Facades\FormBuilder;
use Symfony\Component\HttpFoundation\Response;

trait Create
{

    /**
     * Show the form for creating a new Resource.
     * @return Response
     * @internal param Request $request
     */
    public function create()
    {
        $this->crudAction->failIfNotPermitted('add');

        $form = FormBuilder::build($this->fields);
//        dd($form);
        /*foreach ($this->fields as $field => $options) {
            dd($field);
        }*/
        $buttons = $this->crudAction->renderActions('create', $this->model);

        $this->viewData = [
            'fields' => $this->fields,
            'buttons' => $buttons,
            'model' => $this->model,
            'dataRoute' => $this->getRoute('index'),
            'routePrefix' => $this->routePrefix,
            'success' => true,
            'entityName' => Str::singular($this->getEntityName()),
            'form' => $form
        ];
        if ($this->crudType === Constants::CRUDTYPE_MODAL) {
            $this->viewData['title'] = Str::plural($this->getEntityName());
            $this->viewData['view'] = 'hailstorm::crud-modal.index';
        } else {
            $this->viewData['title'] = 'Add New ' . $this->getEntityName();
            $this->viewData['view'] = $this->createView;
        }


        $this->callHookMethod('creating');

        return $this->responseBuilder->send($this->request, $this->viewData);
    }
}
