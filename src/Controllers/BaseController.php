<?php
/**
 * Created by PhpStorm.
 * User: rashed
 * Date: 21-Jun-17
 * Time: 4:27 PM
 */

namespace Rashidul\Hailstorm\Controllers;

use Illuminate\Container\Container;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Rashidul\Hailstorm\Constants;
use Rashidul\Hailstorm\Crud\Create;
use Rashidul\Hailstorm\Crud\CrudAction;
use Rashidul\Hailstorm\Crud\Data;
use Rashidul\Hailstorm\Crud\Destroy;
use Rashidul\Hailstorm\Crud\Edit;
use Rashidul\Hailstorm\Crud\Index;
use Rashidul\Hailstorm\Crud\ResponseBuilder;
use Rashidul\Hailstorm\Crud\Show;
use Rashidul\Hailstorm\Crud\Store;
use Rashidul\Hailstorm\Crud\Update;
use Rashidul\Hailstorm\Table\DataTableTransformer;
use Yajra\Datatables\Datatables;

abstract class BaseController extends Controller
{
    use ValidatesRequests, Index, Create, Show, Edit,
        Update, Data, Store, Destroy;

    protected $modelClass;
    protected $model;
    protected $dataTable;
    protected $request;
    protected $responseBuilder;

    // data that will be passed into the view
    protected $viewData;

    // query builder object used by datatable
    protected $dataTableQuery;
    protected $dataTableObject;

    // transformer class to be used by datatble
    protected $dataTransformer = DataTableTransformer::class;

    // views
    protected $indexView = 'hailstorm::crud.table';
    protected $createView = 'hailstorm::crud.form';
    protected $detailsView = 'hailstorm::crud.table';
    protected $editView = 'hailstorm::crud.form';

    // class to handle crud actions
    protected $crudAction;

    protected $container;

    protected $crudType = Constants::CRUDTYPE_MODAL;

    /**
     * BaseController constructor.
     * @internal param $formRequest
     * @internal param $dataTable
     */
    public function __construct()
    {
        $this->dataTable = app(Datatables::class);
        $this->responseBuilder = new ResponseBuilder();
        $this->model = new $this->modelClass;
        $this->crudAction = new CrudAction($this->model);
        $this->container = Container::getInstance();

        if (property_exists($this, 'inject')){
            foreach ($this->inject as $property_name => $class){
                if (class_exists($class)){
                    $this->{$property_name} = app($class);
                }
            }
        }

        $this->middleware(function ($request, $next) {
            $this->request = $request;

            $this->callHookMethod('setup');

            return $next($request);
        });
    }

    protected function callHookMethod($name)
    {
        if (method_exists($this, $name))
        {
            $this->container->call([$this, $name]);
        }
    }

    protected function setRedirectUrl()
    {
        if (!array_key_exists('redirect', $this->viewData)) {
            $this->viewData['redirect'] = $this->model->getShowUrl();
        }
    }

    protected function getRoute($route, $id = null) {
        if ($id) return route($this->routePrefix . '.' .$route, $id);
        return route($this->routePrefix . '.' .$route);
    }

    protected function getEntityName() {
        if (property_exists($this, 'entityName')) return $this->entityName;
        return ucfirst($this->routePrefix);
    }
}
