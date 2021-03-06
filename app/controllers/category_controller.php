<?php
class CategoryController extends BaseController{

    public static function categories(){
        $human = self::get_user_logged_in();
        View::make('category/list.html', array(
            'myCategories' => Category::allByOwner($human->id)));
    }

    public static function newCategory(){
        $human = self::get_user_logged_in();
        View::make('category/new.html', array(
            'myCategories'  => Category::allByOwner($human->id),
            'validColors'   => Category::validColors(),
            'validSymbols'  => Category::validSymbols()));
    }

    public static function storeCategory(){
        $params = $_POST;
        $human = self::get_user_logged_in();

        $myCategory = new Category(array(
            'id_owner'      => $human->id,
            'name'          => $params['name'],
            'description'   => $params['description'],
            'color'         => $params['color'],
            'symbol'        => $params['symbol']));
        $errors = $myCategory->errors();
        
        if($errors){
            View::make('category/new.html', array(
                'myCategory'    => $myCategory,
                'validColors'   => BaseModel::validColors(),
                'validSymbols'  => BaseModel::validSymbols(),
                'errors'        => $errors));
        } else {
            $myCategory->save();
            Redirect::to('/categories', array('message' => 'Category added!'));
        }
    }

    public static function viewCategory($id){
        $human = self::get_user_logged_in();
        $myCategory = Category::find($id);
        
        if($myCategory->checkOwner($human->id)){
            View::make('category/view.html', array(
                'myTasks'       => Task::activeByCategory($id),
                'myCategory'    => $myCategory));
        }
    }

    public function removeCategory($id){
        $human = self::get_user_logged_in();
        $myCategory = Category::find($id);

        if($myCategory->checkOwner($human->id)){
            Category::destroy($id);
            Redirect::to('/categories');
        }
    }

    public static function editCategory($id){
        $human = self::get_user_logged_in();
        $myCategory = Category::find($id);

        if($myCategory->checkOwner($human->id)){
            View::make('category/edit.html', array(
                'myCategory'    => $myCategory,
                'validColors'   => BaseModel::validColors(),
                'validSymbols'  => BaseModel::validSymbols()));
        }
    }

    public static function updateCategory($id){
        $params = $_POST;
        $human = self::get_user_logged_in();
        $myCategory = new Category(array(
            'id'            => $id,
            'name'          => $params['name'],
            'description'   => $params['description'],
            'symbol'        => $params['symbol'],
            'color'         => $params['color']));
        
        $errors = $myCategory->errors();
        if($errors){
            View::make('category/edit.html', array(
                'myCategory'    => $myCategory,
                'validColors'   => BaseModel::validColors(),
                'validSymbols'  => BaseModel::validSymbols(),
                'errors'        => $errors));
        } else {
            if($myCategory->checkOwner($human->id)){
                $myCategory->update($id);
                Redirect::to('/category/' . $id . '/view', array(
                    'message'   => 'Category updated!'));
            }
        }
    }
}
