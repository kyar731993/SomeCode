<?php
/**
 * Список сайтов рассылки
 *
 * @author Yaroshevich Konstantin
 */
class ShopsListList
{
    /**
     * Вывод списка сайтов
     */
    public function list(int $count = null)
    {
        $resArray = [];
        // формируем запрос
        $sql = "SELECT * FROM site_list ORDER BY ID DESC";


        // если задано количество выводимое на странице - ограничиваем
        if (isset($count) && $count > 0) {
            $sql .= ' LIMIT ' . $count;
        }

        // выполняем запрос
        $res = getDB()->query($sql);

        foreach ($res as $row) {
            $resArray[] = $row;
        }

        return $resArray;
    }
}