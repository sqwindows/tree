<?php
/**
 * Created by crazyCater
 * User: crazyCater
 * Date: 2019/11/20 15:10
 */

namespace CrazyCater;

class Tree
{
    /**
     * 将格式数组转换为树
     * @param array $list
     * @param integer $level 进行递归时传递用的参数
     */
    private $formatTree; //用于树型数组完成递归格式的全局变量

    private function _toFormatTree($list, $level = 0, $title = 'title')
    {
        foreach ($list as $key => $val) {
            $tmp_str = str_repeat("", $level * 2);
            $tmp_str .= " └─";

            $val['level'] = $level;
            $val['title_show'] = $level == 0 ? $val[$title] . "" : $tmp_str . $val[$title] . "";
            // $val['title_show'] = $val['id'].'|'.$level.'级|'.$val['title_show'];
            if (!array_key_exists('childs', $val)) {
                array_push($this->formatTree, $val);
            } else {
                $tmp_ary = $val['childs'];
                unset($val['childs']);
                array_push($this->formatTree, $val);
                $this->_toFormatTree($tmp_ary, $level + 1, $title); //进行下一层递归
            }
        }
        return;
    }


    public function toFormatTree($Tree = [], $pk = 'id', $pid = 'pid', $title = 'title', $root = 0)
    {
        $this->formatTree = [];
        $this->_toFormatTree($Tree, 0, $title);
        return $this->formatTree;
    }

    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     * @Author: WuFeng <sqwindows@qq.com>
     */
    public function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'childs', $root = 0)
    {
        // 创建Tree
        $tree = [];
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data)
                $refer[$data[$pk]] = &$list[$key];
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                if (!empty($data['pid'])) {
                    $parentId = $data[$pid];
                } else
                    $parentId = 0;
                if ($root == $parentId)
                    $tree[] = &$list[$key];
                else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * 将list_to_tree的树还原成列表
     * @param  array $tree 原来的树
     * @param  string $child 孩子节点的键
     * @param  string $order 排序显示的键，一般是主键 升序排列
     * @param  array $list 过渡用的中间数组，
     * @return array        返回排过序的列表数组
     * @Author: WuFeng <sqwindows@qq.com>
     */
    public function tree_to_list($tree, $child = 'childs', $order = 'id', &$list = [])
    {
        if (is_array($tree)) {
            foreach ($tree as $key => $value) {
                $reffer = $value;
                if (isset($reffer[$child])) {
                    unset($reffer[$child]);
                    $this->tree_to_list($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
            $list = $this->list_sort_by($list, $order, $sortby = 'asc');
        }
        return $list;
    }

    /**
     * 对查询结果集进行排序
     * @access public
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param array $sortby 排序类型
     * asc正向排序 desc逆向排序 nat自然排序
     * @return array
     */
    public function list_sort_by($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = [];
            foreach ($list as $i => $data)
                $refer[$i] = &$data[$field];
            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc': // 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ($refer as $key => $val)
                $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return $list;
    }
}
