<?php

namespace MinhNgoc\ResourceApiLumen;

use Illuminate\Console\Command;

class CreateResourceApi extends Command
{
    private $route_name = null;
    private $version = null;
    private $controller = null;
    private $method = 'all';
    private $model = '';
    private $arr_type = ['index'=>'GET','show'=>'GET','update'=>'PUT','edit'=>'GET','destroy'=>'DELETE','create'=>'GET','store'=>'POST'];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:create {route_name} {--version_route=default} {--controller=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create resource api';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        if (!$this->createOptionCommand()) {
            $this->error('Error!!!');
            return;
        }
        $infor_option = "- Route: $this->route_name \n- Version: $this->version \n- Controller: $this->controller \n- Methods: $this->method \n- Model: $this->model";
        $this->info($infor_option);
        if ($this->confirm('Continue create resource?')) {
            if ($this->createResource()) {

            } else {
                $this->error('Error!!!');
            }
        }
        $this->info('Finish!!!');
        $this->warn('GoodBye!!!');
        //
    }

    function createOptionCommand()
    {
        $this->route_name = $this->argument('route_name');
        $this->version = $this->option('version_route');
        $this->controller = $this->option('controller');
        if ($this->controller == 'null') {
            $this->error('The "--controller" option does not accept a value.');
            return false;
        }
        if ($this->confirm('Enter [no] to create all methods resource api Or [yes] to enter methods?')) {
            $this->method = $this->ask('Enter name methods of controller(Example:show,index,update,store,destroy,...)?');
        }
        if ($this->confirm('Do you wish to create model?')) {
            $this->model = $this->ask('Enter name model?');
        }
        return true;
    }

    function createResource()
    {
        if(!$this->createController()){
            return false;
        }
        $this->createModel();
        $this->createRoute();
        return true;
    }

    function createController()
    {
        $version = strtoupper($this->version);
        $controller = $this->controller;
        $path_class = $this->version=='default'?"App\Http\Controllers\\{$controller}":"App\Http\Controllers\Api\\{$version}\\{$controller}";
        if (class_exists($path_class)) {
            $this->error("Controller $controller existed!!!");
            return false;
        }
        $controller_command = $this->version=='default'?"$controller":"Api/$version/$controller";
        $var = exec("(cd " . base_path() . " && php artisan make:controller $controller_command)");
        $this->comment($var);
        if($this->method=='all'){
            $name_methods = explode(',', 'show,index,update,store,destroy,create');
        }else{
            $name_methods = explode(',', $this->method);
        }
        $methods = '';
        foreach ($name_methods as $method) {
            $methods .= $this->createMethod($method);
        }
        try{
            $path_class = $this->version=='default'?app_path("Http/Controllers/$controller.php"):app_path("Http/Controllers/Api/$version/$controller.php");
            $content = file_get_contents($path_class);
            $content = str_replace('extends Controller','',$content);
            $content = str_replace('use App\Http\Controllers\Controller;','',$content);
            $content = str_replace('}',$methods .'}',$content);
            file_put_contents($path_class,$content);
        }catch (\Exception $error){
            $this->warn($error->getMessage());
            return false;
        }
        return true;

    }

    function createModel(){
        if($this->model!=''){
            exec("(cd " . base_path() . " && php artisan make:model Models/$this->model)");
            $this->warn('Model created successfully.');
        }
    }

    function createMethod($method){

        $type_method = isset($this->arr_type[$method])?$this->arr_type[$method]:'GET';

        $str_comment = "
     /**
     * $type_method $this->version/$this->route_name
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version $this->version
     * `controller` | $this->controller
     * `route_name` | $this->route_name
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu $this->route_name ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong $this->route_name ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi $this->route_name cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang $this->route_name cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
       
      ";
        if($method != "index"){
            $str_comment = "
     /**
     * $type_method $this->version/$this->route_name
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID $this->route_name
     * `@fields` | List fields $this->route_name
     * @return \Illuminate\Http\Response
     */

      ";
        }


        if ($method == 'store') {
            $str_method  = "
    $str_comment
    function $method(Request \$request){
    
    }
        ";
        } else if ($method == 'destroy' || $method == 'show' || $method == 'edit') {
            $str_method = "
     $str_comment
    function $method(\$id){
    
    }
        ";
        } else if ($method == 'update') {
            $str_method = "
    $str_comment
    function $method(\$id,Request \$request){
    
    }
        ";
        } else {
            $str_method = "
    $str_comment
    function $method(){
    
    }
        ";
        }

        return $str_method;

    }

    function createRoute(){
        if($this->method!='all'){
            $access_methods = '';
            $arr_methods = explode(',',$this->method);
            foreach ($arr_methods as $key=>$method){
                if($key==(count($arr_methods)-1)){
                    $access_methods.="'$method'";
                    break;
                }
                $access_methods.="'$method',";
            }
        }
        $str_route = $this->method=='all'?"\$router->resource('$this->route_name', '$this->controller');":"\$router->resource('$this->route_name', '$this->controller',['only' => [$access_methods]]);";
        if($this->version=='default'){
            file_put_contents(base_path('routes/web.php'),$str_route,FILE_APPEND);
        }else{
            if(file_exists(base_path("routes/$this->version.php"))){
                file_put_contents(base_path("routes/$this->version.php"),$str_route,FILE_APPEND);
            }else{
                $file = fopen(base_path("routes/$this->version.php"),"w") or die("Unable to open file!");
                fwrite($file, '<?php '.$str_route);
                fclose($file);
            }
        }
        $this->warn('Route created successfully.');
    }
}
