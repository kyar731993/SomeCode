<?php

/**
 * Форма добавления сайтов в список рассылки
 *
 * @author Yaroshevich Konstantin
 */
class ShopsListForm
{
    /**
     * Проверка на присутсвие в базе записей с аналогичными, похожими url
     * @param $url урл адрес сайта
     * @return array если нет повторов = пустой, иначе возвращает что удалось найти
     */
    public function isNew(string $url)
    {
        // убираем протокол, если присутствует
        $newUrl = $this->deleteProtocol($url);
        // формируем запрос
        $sql = "SELECT * FROM test_sites_list WHERE address = '" . $url .
          "' OR address LIKE '%" . $newUrl . "%'";
        // выполняем запрос
        $res = getDB()->query($sql);

        return $res->fetch();
    }

    /**
     * Удаление протокола из адреса сайта, при наличии
     * @param string $url адрес сайта
     * @return string $urlWithoutProtocols
     */
    private function deleteProtocol(string $url)
    {
        $posFirst = strrpos($url, '://', 0);

        if ($posFirst) {
            $urlWithoutProtocols = substr($url, $posFirst + 3);
        } else {
            $urlWithoutProtocols = $url;
        }

        return $urlWithoutProtocols;
    }

    /**
     * Сохранение данных формы
     * @param $dataArray данные формы (массив $_POST)
     * @return bool при успешном добавлении true
     */
    public function saveInDB(array $dataArray)
    {
        // добавление в базу
        $sql = "INSERT INTO test_sites_list
            SET 
                address = :address,
                kind_of_activity = :kind_of_activity,
                email = :email,
                phone = :phone,
                created_by = :created_by,
                date_of_creation = NOW()";

        $stmt = getDB()->prepare($sql);
        // убираем протокол, если присутствует
        $newUrl = $this->deleteProtocol($dataArray['addressSite']);

        $stmt->execute(array(
          ':address' => $newUrl,
          ':kind_of_activity' => $dataArray['kindOfActivitiesSite'],
          ':email' => $dataArray['emailSite'],
          ':phone' => $dataArray['phoneSite'],
          ':created_by' => $_SESSION['login'],
        ));

        return true;
    }
}