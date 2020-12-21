<?php
namespace common\dao;

use think\db\exception\DbException;
use think\facade\Log;
use think\Model;

abstract class Dao
{
    /**
     * @var Model
     * @see Model
     */
    protected $class = null;

    /**
     * 静态调用入口
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callstatic($method, $args)
    {
        $sub_class = get_called_class();
        return (new $sub_class())->class::$method(...$args);
    }

    /**
     * 查询数据集
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     */
    public function getList($where, $field = '*', $page = 1, $limit = 100, $order = "id DESC")
    {
        $query = $this->class::where($where)->field($field);
        $page && $limit && $query = $query->page($page)->limit($limit);
        $order && $query = $query->order($order);
        try {
            $result = $query->select();
            return $result->isEmpty() ? [] : $result->toArray();
        } catch (DbException $dbException) {
            Log::error("数据库查询失败：".$dbException->getMessage());
            return [];
        }
    }

    /**
     * 添加数据
     * @param array $data
     * @return mixed
     */
    public function insertGetId($data)
    {
        return (new $this->class())->insertGetId($data);
    }

    /**
     * 更新数据
     * @param array $data
     * @param array $where
     * @return Model
     */
    public function updateByWhere($data, $where)
    {
        return $this->class::update($data, $where);
    }

    /**
     * 根据条件删除数据
     * @param array $where
     * @return bool
     */
    public function deleteByWhere($where)
    {
        return $this->class::where($where)->delete();
    }

    /**
     * 根据主键删除数据
     * @param int $id
     * @return mixed
     */
    public function deleteById($id)
    {
        return (new $this->class())->delete($id);
    }

    /**
     * 根据主键删除数据（批量）
     * @param array $ids
     * @return mixed
     */
    public function deleteByIds(array $ids)
    {
        return (new $this->class())->delete($ids);
    }

    /**
     * 根据条件获取数量
     * @param array $where
     * @return int
     */
    public function getCountByWhere($where)
    {
        return $this->class::where($where)->count();
    }

    /**
     * 根据ID获取单个信息
     * @param int $id
     * @return array
     */
    public function getInfoById($id)
    {
        $result = $this->class::where(['id' => $id])->findOrEmpty();
        return $result ? $result->toArray() : [];
    }

    /**
     * 根据ID获取单个信息
     * @param int $id
     * @return array
     */
    public function getInfoByWhere($where)
    {
        $result = $this->class::where($where)->findOrEmpty();
        return $result ? $result->toArray() : [];
    }

    /**
     * 根据单个字段信息
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function value($where, $field)
    {
        return $this->class::where($where)->value($field);
    }
}