<?php

namespace app\Repositories;

use app\core\Tech;
use app\models\Contacts;

class ContactRepository
{
    public static function getFields(int $userId, $default = ''): array
    {
        $contactsData = Contacts::getAll(['user_id' => $userId]);
        $contacts = [
            'email' => $default,
            'telegram' => $default,
            'phone' => $default,
        ];
        foreach ($contactsData as $num => $contactData) {
            if (!isset($contacts[$contactData['type']])) continue;
            $contacts[$contactData['type']] = $contactData['contact'];
        }
        return $contacts;
    }
    public static function formatUserContacts(array $contacts)
    {
        $result = [];
        foreach ($contacts as $num => $contact) {
            $result[$contact['type']] = $contact['contact'];
        }
        return $result;
    }
    public static function wrapLinks(array $data): array
    {
        foreach ($data as $type => $value) {
            $data[$type.'__value'] = '';
            if (empty($value) || $value === 'No data')
                continue;

            $data[$type.'__value'] = $value;

            if ($type === 'email') {
                $data[$type] = "<a href='mailto:$value' target='_blank'>$value</a>";
                continue;
            }
            if ($type === 'telegram') {
                $data[$type] = "<a href='https://t.me/$value' target='_blank'>@$value</a>";
                continue;
            }
            if ($type === 'phone') {
                $phone = preg_replace('/[^0-9]/', '', $value);
                preg_match('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', $phone, $phoneParts);
                $phoneFormatted = sprintf('+%s (%s) %s-%s-%s', $phoneParts[1], $phoneParts[2], $phoneParts[3], $phoneParts[4], $phoneParts[5]);
                $data[$type] = "<a href='tel:+$value' target='_blank'>$phoneFormatted</a>";
                continue;
            }
        }
        return $data;
    }
    public static function edit(int $userId, array $contacts): bool
    {
        $contactsData = Contacts::getAll(['user_id' => $userId]);
        foreach ($contactsData as $num => $contactData) {
            $type = $contactData['type'];
            if (!isset($contacts[$type])) continue;
            if ($contacts[$type] == $contactData['contact']) {
                unset($contacts[$type]);
                continue;
            }
            Contacts::remove($contactData['id']);
        }
        if (!empty($contacts)) {
            foreach ($contacts as $type => $contact) {
                if (!$contact) continue;
                Contacts::add([
                    'user_id' => $userId,
                    'type' => $type,
                    'contact' => $contact,
                ]);
            }
        }
        return true;
    }
    public static function findContactByType(string $type, array $contacts)
    {
        foreach ($contacts as $index => $data) {
            if ($data['type'] !== $type) continue;
            if (!empty($data['data'])) {

                if (empty($data['data']['approve']))
                    return $data;

                if ($data['data']['approve']['expired'] < $_SERVER['REQUEST_TIME']) {
                    unset($data['data']['approve']);
                }
            }
            return $data;
        }
        return false;
    }
    public static function setApproveData(string $type, array $contacts)
    {
        $contact = self::findContactByType($type, $contacts);

        if ($contact === false)
            return false;

        if (empty($contact['data']['approve']['code'])) {
            $_SESSION['approve_code'] = Tech::getCode(json_encode($contacts));
        } else {
            $_SESSION['approve_code'] = $contact['data']['approve']['code'];
        }

        if (!empty($contact['data']['approved'])) return $contact;

        $hash = sha1($_SESSION['approve_code'] . $_SERVER['REQUEST_TIME']);
        $contact['data']['approve'] = [
            'hash' => $hash,
            'code' => $_SESSION['approve_code'],
            'expired' => $_SERVER['REQUEST_TIME'] + 3600,
        ];
        Contacts::edit(['data' => $contact['data']], ['id' => $contact['id']]);

        return $contact;
    }
    public static function checkApproved($userId)
    {
        $contacts = Contacts::getByUserId($userId);
        if (empty($contacts)) return [];

        $approved = [];
        foreach ($contacts as $num => $data) {
            if ($data['type'] === 'telegramid') {
                $approved[$data['type']] = true;
                continue;
            }

            if (empty($data['data'])) continue;

            if (empty($data['data']['approved'])) continue;

            $approved[$data['type']] = true;
        }
        return $approved;
    }
    public static function getApproved($userId)
    {
        $contacts = Contacts::getByUserId($userId);
        $approved = [];
        foreach ($contacts as $num => $data) {
            if ($data['type'] === 'telegramid') {
                $approved[$data['type']] = $data['contact'];
                continue;
            }
            if (empty($data['data'])) continue;
            $data['data'] = json_decode($data['data'], true);
            if (isset($data['data']['approved'])) {
                $approved[$data['type']] = $data['contact'];
            }
        }
        return $approved;
    }
}
