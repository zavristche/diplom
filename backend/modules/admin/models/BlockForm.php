<?php

namespace app\modules\admin\models;

use app\models\block\BlockType;
use app\models\block\Block;
use app\models\block\BlockStatus;
use app\models\user\User;
use Yii;

class BlockForm extends \yii\base\Model
{
    public int $block_type_id;
    public int $user_id;
    public int $block_status_id;
    public int $block_reason_id;
    public string $other_reason = '';
    public $time_unblock;
    public bool $check = false;

    const SCENARIO_BLOCK = 'block';
    const SCENARIO_BLOCK_PERMANENT = 'permanent_block';
    const SCENARIO_OTHER_BLOCK = 'other_block';
    const SCENARIO_OTHER_BLOCK_PERMANENT = 'other_permanent_block';

    public function rules()
    {
        return [
            [[ 'block_type_id', 'block_status_id', 'user_id'], 'required'],
            [['block_reason_id', 'block_type_id'], 'integer'],
            [['other_reason'], 'string', 'max' => 255],
            ['time_unblock', 'safe'],

            [['block_reason_id', 'time_unblock'], 'required', 'on' => self::SCENARIO_BLOCK],
            [['block_reason_id'], 'required', 'on' => self::SCENARIO_BLOCK_PERMANENT],
            [['other_block', 'time_unblock'], 'required', 'on' => self::SCENARIO_OTHER_BLOCK],
            [['other_block'], 'required', 'on' => self::SCENARIO_OTHER_BLOCK_PERMANENT],

            ['check', 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Пользователь',
            'block_type_id' => 'Тип блокировки',
            'block_status_id' => 'Статус блокировки',
            'block_reason_id' => 'Причина блокировки',
            'other_block' => 'Иная причина блокировки',
            'time_unblock' => 'Дата разблокировки',
        ];
    }


    public function block($id)
    {
        $this->user_id = $id;
        $this->block_status_id = BlockStatus::getOne('Действует');
        
        if(!empty($this->check)){
            if($this->block_type_id == BlockType::getOne('Навсегда')){
                $this->scenario = self::SCENARIO_OTHER_BLOCK_PERMANENT;
            } else {
                $this->scenario = self::SCENARIO_OTHER_BLOCK;
            }
        } else {
            if($this->block_type_id == BlockType::getOne('Навсегда')){
                $this->scenario = self::SCENARIO_BLOCK_PERMANENT;
            } else {
                $this->scenario = self::SCENARIO_BLOCK;
            }
        }

        if($this->validate()){
            // $user = User::findIdentity($id);
            $block = new Block();

            foreach ($this->attributes as $attribute => $value) {
                if ($value !== null && $block->hasAttribute($attribute)) {
                    $block->$attribute = $value;
                }
            }
            
            $block->save();
        }

        return empty($this->errors) ? $block : $this->errors;
    }
}
