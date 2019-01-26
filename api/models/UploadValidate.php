<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Class UploadValidate 文件上传验证
 * 使用model验证文件上传字段
 * ```
 * $model = new UploadValidate($config_name);
 * ```
 *
 * @package common\models
 * @author  windhoney
 * @package common\models
 */
class UploadValidate extends Model
{
    
    /**
     * @var string 表单字段名
     */
    public $file;
    /**
     * @var array|string 扩展名
     */
    public $extensions = ['jpg', 'png', 'jpeg', 'jpe'];
    /**
     * @var int 文件大小 最大值  2M
     */
    public $max_size = 2 * 1024 * 1024;
    /**
     * @var int 文件大小 最小值  单位字节
     */
    public $min_size = 1;
    /**
     * @var array|string  MIME TYPE
     */
    public $mime_type = ['image/*', 'application/pdf'];
    /**
     * @var string 上传失败后返回信息
     */
    public $message = '上传失败，请检查文件类型及文件大小';
    
    /**
     * UploadValidate constructor.
     *
     * @param string $config_name `@app/config/params.php` 文件上传验证配置项名称
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * @inheritdoc 验证规则
     */
    public function rules()
    {
        $file_rule = [['file'], 'file'];
        if ($this->extensions) {
            $file_rule['extensions'] = $this->extensions;
        }
        if ($this->mime_type) {
            $file_rule['mimeTypes'] = $this->mime_type;
        }
        if ($this->max_size) {
            $file_rule['maxSize'] = $this->max_size;
        }
        if ($this->min_size) {
            $file_rule['minSize'] = $this->min_size;
        }
        if ($this->message) {
            $file_rule['message'] = $this->message;
        }
        $rules = [$file_rule];
        
        return $rules;
    }
}