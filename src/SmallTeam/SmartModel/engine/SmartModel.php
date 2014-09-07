<?php
namespace SmallTeam\SmartModel;

use LaravelBook\Ardent\Ardent,
    Illuminate\Support\Facades\DB,
    Closure;

class SmartModel extends Ardent {

    public static $files = array();

    public function afterSave() {
        if(isset(static::$files) && is_array(static::$files) && !empty(static::$files)) {
            FilesHelper::create($this)->saveFiles()->loadFiles();
        }
        return true;
    }

    /**
     * Save the model to the database.
     *
     * @param array   $rules
     * @param array   $customMessages
     * @param array   $options
     * @param Closure $beforeSave
     * @param Closure $afterSave
     *
     * @return bool
     * @see Ardent::forceSave()
     */
    public function save(array $rules = array(),
                         array $customMessages = array(),
                         array $options = array(),
                         Closure $beforeSave = null,
                         Closure $afterSave = null
    ) {
        if(isset(static::$files) && is_array(static::$files) && !empty(static::$files)) {
            foreach (static::$files as $alias => $file_info) {
                unset($this->$alias);
            }
        }
        parent::save($rules, $customMessages, $options, $beforeSave, $afterSave);
        return true;
    }

    /**
     * Get files folder
     *
     * @return null|string
     * */
    public function getFilesFolder() {
        if(isset(static::$files) && is_array(static::$files) && !empty(static::$files)) {
            return FilesHelper::create($this)->getFilesFolder();
        }

        return null;
    }

    /**
     * Remove model files
     *
     * @param string $alias File alias
     * @param string $name File name
     * @return SmartModel
     * */
    public function removeFile($alias, $name) {
        if(isset(static::$files) && is_array(static::$files) && !empty(static::$files)) {
            FilesHelper::create($this)->removeFile($alias, $name);
        }
        return $this;
    }

    /**
     * Load model files
     *
     * @return SmartModel
     * */
    public function loadFiles() {
        if(isset(static::$files) && is_array(static::$files) && !empty(static::$files)) {
            FilesHelper::create($this)->loadFiles();
        }
        return $this;
    }

    /**
     * Save model files
     *
     * @return SmartModel
     * */
    public function saveFiles() {
        if(isset(static::$files) && is_array(static::$files) && !empty(static::$files)) {
            FilesHelper::create($this)->saveFiles()->loadFiles();
        }
        return $this;
    }

    public function mergeData(array $data) {
        if(!is_array($data) || empty($data)) {
            return false;
        }

        $structure = $this->getStructure();

        foreach ($data as $key => $item) {
            if(!isset($structure['columns']) || !is_array($structure['columns']) || !array_key_exists($key, $structure['columns'])) {
                continue;
            }
            $this->$key = $item;
        }

        return $this;
    }

    /**
     * Build model structure by DB table
     *
     * @return array
     * */
    public function getStructure() {
        $table = $this->getTable();
        $structure = DB::select('DESCRIBE '.  $table .'');
        $result = array();

        if(is_array($structure) && !empty($structure)) {
            $columns = array();
            foreach ($structure as $item) {
                $columns[$item->Field] = array(
                    'type' => $item->Type,
                    'default' => $item->Default,
                    'not_null' => $item->Null == 'NO' ? true : false,
                );
            }

            $result = array(
                'table' => $table,
                'primary_key' => $this->getKeyName(),
                'columns' => $columns,
            );
        }
        return $result;
    }
}