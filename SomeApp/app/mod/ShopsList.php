<?php

/**
 * Контроллер добавления / вывода списка сайтов рассылки
 *
 * любой public метод является action, который могут вызвать с фронтэнда
 * @author Yaroshevich Konstantin
 */
class ShopsList extends Module
{
    /**
     * Отображает список сайтов рассылки согласно указанному количеству
     */
    public function showList()
    {
        if (!hasAccess('просмотр списка сайтов рассылки')) {
            throw new AccessDeniedException('У вас нет прав на просмотр данной страницы');
        }

        $listLimited = new ShopsListList();
        $shopsListLimited = $listLimited->list(100);
        $this->twigVars['shopsList'] = $shopsListLimited;
    }

    /**
     * Отображает полный список сайтов рассылки
     */
    public function showall()
    {
        if (!hasAccess('просмотр списка сайтов рассылки')) {
            throw new AccessDeniedException('У вас нет прав на просмотр данной страницы');
        }

        $list = new ShopsListList();
        $shopsList = $list->list();
        $this->twigVars['shopsList'] = $shopsList;
    }

    /**
     * Отображает форму ввода с полями: url для проверки адреса / все поля для добавления в бд
     */
    public function showForm()
    {

    }

    /**
     * Валидация полей формы
     * @return json строка ок / error
     */
    public function checkForm()
    {
        if (!isset($_POST)) {
            $res['result'] = 'error';
            $res['message'] = 'Массив POST пуст';
            return json_encode($res);
        }

        // фильтрация данных формы
        $formVariables = $this->filterForm($_POST);
        // Массив ошибок в форме
        $validateErrors = $this->validate($formVariables);

        // Если валидация не удалась, выводим ошибки
        if (!empty($validateErrors)) {
            $validateErrors['result'] = 'error';
            return json_encode($validateErrors);
        }

        $check = new ShopsListForm();
        $checkResult = $check->isNew($_POST['addressSite']);

        // Валидация пройдена, проверяем наличие совпадений в базе (если нет = false)
        if (!$checkResult) {
            $res['result'] = 'ok';
            $res['message'] = 'Похожих адресов не обнаружено';
            // иначе выводим сообщение о совпадении адресов
        } else {
            $res['result'] = 'error';
            $res['message'] = 'Сайт ' . $_POST['addressSite'] . ' уже существует';
        }

        return json_encode($res);
    }

    /**
     * Сохранение формы в БД
     * @return json строка ок / error
     */
    public function saveForm()
    {
        $checkResult = json_decode($this->checkForm(), true);

        // если поля некорректно заполнены, выводим ошибки, полученные из validate($formVariables); вызванного в checkForm()
        if ($checkResult['result'] == 'error') {
            $res['result'] = $checkResult['result'];
            $res['message'] = $checkResult['message'];

            return json_encode($res);
        }

        // если поля корректно заполнены
        // фильтрация данных формы
        $formVariables = $this->filterForm($_POST);
        $addToDB = new ShopsListForm();

        // добавляем в БД
        if ($addToDB->saveInDB($formVariables)) {
            $res['result'] = 'ok';
            $res['message'] = 'Сайт успешно добавлен';
        } else {
            $res['result'] = 'error';
            $res['message'] = 'При добавлении сайта в БД произошла ошибка';
        }

        return json_encode($res);
    }

    /**
     * Санация данных формы
     * @param $srcData массив данных из формы
     * @return array данные прошедшие trim
     */
    private function filterForm($srcData)
    {
        $res = [];
        foreach ($srcData as $key => $item) {
            $res[$key] = trim($item);
        }

        return $res;
    }

    /**
     * Валидация формы
     * @param $data данные формы (массив $_POST)
     * @return array formErrors или если ошибок нет, то пустой массив
     */
    private function validate($data)
    {
        $errors = [];

        if (isset($data['addressSite'])) {
            if (!filter_var($data['addressSite'], FILTER_VALIDATE_URL)) {
                $patternAddressSite = "/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/";
                if (!preg_match($patternAddressSite, $data['addressSite'])) {
                    $errors['message'] = 'Некорректный Адрес Сайта (test.com, http://test.com, https://test.com)';
                }
            }
        }

        if (isset($data['kindOfActivitiesSite'])) {
            $patternkindOfActivitiesSite = "/^([а-яА-ЯЁё_ ,]+)$/u";
            if (!preg_match($patternkindOfActivitiesSite, $data['kindOfActivitiesSite'])) {
                isset($errors['message']) ? $errors['message'] .= ', Вид Деятельности' :
                  $errors['message'] = 'Некорректно указан Вид Деятельности (информационная, социальная)';
            }
        }

        if (isset($data['emailSite'])) {
            if (!filter_var($data['emailSite'], FILTER_VALIDATE_EMAIL)) {
                isset($errors['message']) ? $errors['message'] .= ', E-mail адрес' :
                  $errors['message'] = 'Некорректный E-mail адрес (test@test.ru, test-test@test-test.com)';
            }
        }

        if (isset($data['phoneSite'])) {
            $patternPhone = "/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/";
            if (!preg_match($patternPhone, $data['phoneSite'])) {
                isset($errors['message']) ? $errors['message'] .= ', Телефон' :
                  $errors['message'] = 'Некорректно указан Телефон (89111234567, +79111234567)';
            }
        }

        return $errors;
    }
}