<?

namespace app\Repositories;

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
    public static function wrapLinks(array $data): array
    {
        foreach ($data as $type => $value) {
            if ($type === 'email') {
                $data[$type] = "<a href='mailto:$value' target='_blank'>$value</a>";
            }
            if ($type === 'telegram') {
                $data[$type] = "<a href='https://t.me/$value' target='_blank'>@$value</a>";
            }
            if ($type === 'phone') {
                $data[$type] = "<a href='tel:$value' target='_blank'>$value</a>";
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
    public static function setApproveData(string $type, array $contacts)
    {

        $contact = false;
        foreach ($contacts as $index => $data) {
            if ($data['type'] !== $type) continue;
            if (!empty($data['data'])) {
                $data['data'] = json_decode($data['data'], true);
                if (isset($data['data']['approve']))
                    return $data;
            }
            $contact = $data;
            break;
        }

        if ($contact === false)
            return false;

        do {
            $code = preg_replace('/[^0-9]/', '', sha1(json_encode($contacts) . microtime()));
        } while (strlen($code) < 5);

        if (strlen($code) < 8)
            $code = str_pad($code, 8, '0');
        else
            $code = substr($code, 0, 8);

        $hash = sha1($code . $_SERVER['REQUEST_TIME']);

        $contact['data']['approve'] = [
            'code' => $code,
            'hash' => $hash,
        ];

        Contacts::edit(['data' => $contact['data']], ['id' => $contact['id']]);

        return $contact;
    }
    public static function checkApproved($userId)
    {
        $contacts = Contacts::getByUserId($userId);
        $approved = [];
        foreach ($contacts as $num => $data) {
            if (empty($data['data'])) continue;
            $data['data'] = json_decode($data['data'], true);
            if (isset($data['data']['approved'])) {
                $approved[$data['type']] = true;
            }
        }
        return $approved;
    }
    public static function getApproved($userId)
    {
        $contacts = Contacts::getByUserId($userId);
        $approved = [];
        foreach ($contacts as $num => $data) {
            if (empty($data['data'])) continue;
            $data['data'] = json_decode($data['data'], true);
            if (isset($data['data']['approved'])) {
                $approved[$data['type']] = $data['value'];
            }
        }
        return $approved;
    }
}
