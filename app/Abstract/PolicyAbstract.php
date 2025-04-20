<?php

namespace App\Abstract;

use App\Traits\ErrorManagementTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

abstract class GlobalPolicyAbstract{

    use ErrorManagementTrait;

    protected string $_forClass;

    public function getRightsForModel(string|Model $model = ""): string|false{
        if($this->_forClass && !$model)
            $model = $this->_forClass;
        if($modelName = $this->getModelName($model))
            return Auth::user()->role->{$modelName} ?? false;
        else
            return false;
    }

    public function canDoAll(string $rightString, string|Model $model = ""): bool{
        $canDo = true;
        $rightField = $this->extractRightField($model);
        $rights = str_split($rightString);
        foreach ($rights as $right){
            if(!str_contains($rightField, $right)){
                $canDo = false;
                break;
            }
        }
        return $canDo;
    }

    public function canReadAll(string|Model $model = ""){
        return str_contains($this->extractRightField($model), 'r');
    }
    public function canWriteAll(string|Model $model = ""){
        return str_contains($this->extractRightField($model), 'w');
    }
    public function canDeleteAll(string|Model $model = ""){
        return str_contains($this->extractRightField($model), 'd');
    }

    protected function extractRightField(string|Model $model = ""): string{
        if($this->_forClass && !$model)
            $model = $this->_forClass;

        $modelName = $this->getModelName($model);
        return Auth::user()->role->{$modelName};
    }

    protected function getModelName(string|Model $model = ""): string|false{
        if($this->_forClass && !$model)
            $model = $this->_forClass;

        $modelName = false;

        if($this->_forClass && !$model)
            $model = $this->_forClass;

        if(is_string($model)){
                $modelName = class_basename($model);
        }
        else{
            $className = Str::studly($model);
            $pathName = 'App\\Models\\'.$className;
            if(class_exists($pathName) && is_a($pathName, Model::class, true))
                $modelName = $className;
            else
                return false;
        }
        return $modelName;
    }
}
