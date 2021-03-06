<?php
class Task extends BaseModel{
    public $id, $id_tasklist, $name, $description, $duedate, $priority, $status, $archived, $deleted, $categories;
    public function __construct($attributes){
        parent::__construct($attributes);
        $this->validators = array('validate_name', 'validate_description');
    }

    public static function newFromParams($row){
        $task = new Task(array(
            'id'            => $row['id'],
            'id_tasklist'   => $row['id_tasklist'],
            'name'          => $row['name'],
            'description'   => $row['description'],
            'duedate'       => $row['duedate'],
            'priority'      => $row['priority'],
            'status'        => $row['status'],
            'archived'      => $row['archived'],
            'deleted'       => $row['deleted']
            ));
        $task->categories = Category::allByTask($row['id']);
        return $task;
    }

    public static function find($id){
        $query = DB::connection()->prepare('
            SELECT * 
              FROM task 
             WHERE id = :id 
             LIMIT 1');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute(); 
        $row = $query->fetch();

        if($row){
            return self::newFromParams($row);
        }
        return null;
    }

    public static function all($id_owner){
        $query = DB::connection()->prepare('
            SELECT * 
              FROM task 
             WHERE id_tasklist 
                IN (SELECT id 
                      FROM tasklist 
                     WHERE id_owner = :id_owner)');
        $query->bindValue(':id_owner', $id_owner, PDO::PARAM_INT);
        $query->execute();
        $rows = $query->fetchAll();

        $tasks = array();
        foreach($rows as $row){
            $tasks[]=self::newFromParams($row);
        }
        return $tasks;
    }

    public static function activeInTasklist($id_tasklist){
        $query = DB::connection()->prepare('
            SELECT * 
              FROM task 
             WHERE id_tasklist = :id_tasklist
               AND archived = false
               AND deleted = false
          ORDER BY id DESC');
        $query->bindValue(':id_tasklist', $id_tasklist, PDO::PARAM_INT);
        $query->execute();
        $rows = $query->fetchAll();

        $tasks = array();
        foreach($rows as $row){
            $tasks[]=self::newFromParams($row);
        }
        return $tasks;
    }

    public static function archivedByOwner($id_owner){
        $query = DB::connection()->prepare('
            SELECT * 
              FROM task 
             WHERE id_tasklist IN (SELECT id FROM tasklist WHERE id_owner = :id_owner) 
               AND archived = TRUE
          ORDER BY id DESC');
        $query->bindValue(':id_owner', $id_owner, PDO::PARAM_INT);
        $query->execute();
        $rows = $query->fetchAll();

        $tasks = array();
        foreach($rows as $row){
            $tasks[]=self::newFromParams($row);
        }
        return $tasks;      
    }

    public function save(){
        $query = DB::connection()->prepare('
            INSERT INTO Task (id_tasklist, name, description, duedate, priority, status) 
                 VALUES (:id_tasklist, :name, :description, :duedate, :priority, :status)
              RETURNING id');
        $query->bindValue(':id_tasklist',   $this->id_tasklist, PDO::PARAM_STR);
        $query->bindValue(':name',          $this->name,        PDO::PARAM_STR);
        $query->bindValue(':description',   $this->description, PDO::PARAM_STR);
        // If duedate is empty, mark it null (empty string will crash postgresql timestamp)        
        if($this->duedate){ 
            $query->bindValue(':duedate', $this->duedate, PDO::PARAM_STR);
        }else{ 
            $query->bindValue(':duedate', null, PDO::PARAM_STR);
        }
        $query->bindValue(':priority',      $this->priority,    PDO::PARAM_INT);
        $query->bindValue(':status',        $this->status,      PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch();
        $this->id = $row['id'];
    }

    public function update($id){
        $query = DB::connection()->prepare('
            UPDATE task 
               SET name         =:name,
                   id_tasklist  =:id_tasklist, 
                   description  =:description, 
                   duedate      =:duedate, 
                   priority     =:priority, 
                   status       =:status 
             WHERE id           =:id');
        $query->bindValue(':name',          $this->name,        PDO::PARAM_STR);
        $query->bindValue(':id_tasklist',   $this->id_tasklist, PDO::PARAM_INT);
        $query->bindValue(':description',   $this->description, PDO::PARAM_STR);
        // If duedate is empty, mark it null (empty string will crash postgresql timestamp)        
        if($this->duedate){ 
            $query->bindValue(':duedate', $this->duedate, PDO::PARAM_STR);
        }else{ 
            $query->bindValue(':duedate', null, PDO::PARAM_STR);
        }
        $query->bindValue(':priority',      $this->priority,    PDO::PARAM_INT);
        $query->bindValue(':status',        $this->status,      PDO::PARAM_INT);
        $query->bindValue(':id',            $id,                PDO::PARAM_INT);
        $query->execute();
    }

    public function destroy(){
        $query = DB::connection()->prepare('DELETE FROM Task WHERE id = :id');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    public function start(){
        $query = DB::connection()->prepare('UPDATE Task SET status = 1 WHERE id=:id');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
    }
    
    public function complete(){
        $query = DB::connection()->prepare('UPDATE Task SET status = 2 WHERE id=:id');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    public function archive(){
        $query = DB::connection()->prepare('UPDATE task SET archived = true WHERE id=:id');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    public function revert(){
        $query = DB::connection()->prepare('UPDATE task SET archived = false WHERE id=:id');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    public function setPriority($priority){
        $query = DB::connection()->prepare('UPDATE task SET priority = :priority WHERE id=:id');
        $query->bindValue(':id',        $this->id,  PDO::PARAM_INT);
        $query->bindValue(':priority',  $priority,  PDO::PARAM_INT);
        $query->execute();
    }

    public static function activeByCategory($id_category){
        $query = DB::connection()->prepare('
            SELECT * 
              FROM task 
         LEFT JOIN taskCategory 
                ON task.id = taskcategory.id_task 
             WHERE id_category = :id_category
               AND archived = false
               AND deleted = false
          ORDER BY id DESC');
        $query->bindValue(':id_category', $id_category, PDO::PARAM_INT);
        $query->execute();
        $rows = $query->fetchAll();

        $tasks = array();
        foreach($rows as $row){
            $tasks[]=self::newFromParams($row);
        }
        return $tasks;
    }

    public function checkOwner($id_human){
        $query = DB::connection()->prepare('
            SELECT id_owner
              FROM tasklist
             WHERE id = :id_tasklist
             LIMIT 1');
        $query->bindValue('id_tasklist', $this->id_tasklist, PDO::PARAM_INT);
        $query->execute();
        $row = $query->fetch();

        return $row['id_owner'] == $id_human;
    }
}
