<?php
/**
 * Created by PhpStorm.
 * User: SZL4ZSY
 * Date: 7/4/2018
 * Time: 11:03 AM
 */
namespace app\Entities;

const  dsn="mysql:host=223.112.88.211;port=4950;dbname=vpro";
class db{

    public $pdo;
    function __construct()
    {
        $this->pdo=new \PDO(dsn,"mark","7777777y");
    }

    function getDataBySql($sql) //偷懒写了一个函数  莫纠结
    {
        $sth = $this->pdo->prepare($sql);
        $sth->execute();

        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        return $sth->fetchAll();
    }
    function getData($where,$tbName,$returnSql=false)
    {
        $whereStr="";
        foreach($where as $key=>$value)
        {
            $whereStr.=" and ";
            if(is_int($value))
                $whereStr.=$key."=".$value;
            else
                $whereStr.=$key."='".$value."'";
        }
        $sql="select * from $tbName where 1=1 ".$whereStr;

        if($returnSql)
            return $sql;
        $sth = $this->pdo->prepare($sql);
        $sth->execute();

        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        return $sth->fetchAll();
    }
    function saveToDB($data,$tbName,$returnSql=false)
    {
        //$tbName，仅仅是为了演示方便，做的一个 通用的SQL拼凑,这里不错任何关键字过滤和危险字符过滤，莫纠结
        //大家可以使用自己喜欢的ORM来做
        //$sql=true 则返回SQL，不执行

        $sql_fields="";
        $sql_values="";
        foreach($data as $key=>$value)
        {
            if(strpos($key,"__")!==false && strpos($key,"__")===0) continue;//__开头的代表是内部变量
            //拼凑字符串
            if($sql_fields!="")
                $sql_fields.=",";
            $sql_fields.=$key;
            if($sql_values!="")
                $sql_values.=",";
            $sql_values.="'".$value."'";
        }

        $sql="insert into $tbName(".$sql_fields.") values(".$sql_values.")";

        if($returnSql)
            return $sql;
        return $this->pdo->exec($sql);// 返回受影响的行
    }


}