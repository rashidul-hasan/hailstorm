<?php
/**
 * Created by PhpStorm.
 * User: rashidul
 * Date: 14-Oct-17
 * Time: 8:16 PM
 */

namespace Rashidul\Hailstorm\Crud;


use Illuminate\Database\QueryException;
use Rashidul\Hailstorm\FieldsCollection;
use Rashidul\Hailstorm\Model\ModelHelper;

trait Store
{

    /**
     * Store a newly created resource in storage.
     * @return Response
     * @internal param Request $request
     * @internal param Request $request
     */
    public function store()
    {

        $fieldsCollection = new FieldsCollection($this->fields);
        $this->request->validate($fieldsCollection->getValidationRules());

        $this->model = ModelHelper::fillWithRequestData($this->model, $this->request, $fieldsCollection->getFormFields());

        $this->callHookMethod('storing');

        try{
            if ($this->model->save()){
                $this->viewData['success'] = true;
                $this->viewData['message'] = $this->model->getEntityName() . ' Created!';
                $this->viewData['item'] = $this->model;

                // many to many
                ModelHelper::updateManyToManyRelations($this->model, $this->request);

                $this->callHookMethod('stored');

            } else {
                $this->viewData['success'] = false;
                $this->viewData['message'] = 'Something went wrong';
            }
        } catch (QueryException $e){
            $this->viewData['message'] = $e->getMessage();
            $this->viewData['success'] = false;
        }

        // set redirect url
        $this->setRedirectUrl();

        return $this->responseBuilder->send($this->request, $this->viewData);

    }

}
