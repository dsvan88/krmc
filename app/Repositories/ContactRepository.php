<?
namespace app\Repositories;

use app\models\Contacts;

class ContactRepository
{
    public static function getFields(int $userId, $default=''): array{
        $contactsData = Contacts::getAll(['user_id' => $userId]);
        $contacts = [
            'email' => $default,
            'telegram' => $default,
            'phone' => $default,
        ];
        foreach($contactsData as $num=>$contactData){
            if (!isset($contacts[$contactData['type']])) continue;
            $contacts[$contactData['type']] = $contactData['contact'];
        }
        return $contacts;
    }
    public static function wrapLinks(array $data): array{
        foreach($data as $type=>$value){
            if ($type === 'email'){
                $data[$type] = "<a href='mailto:$value' target='_blank'>$value</a>";
            }
            if ($type === 'telegram'){
                $data[$type] = "<a href='https://t.me/$value' target='_blank'>@$value</a>";
            }
            if ($type === 'phone'){
                $data[$type] = "<a href='tel:$value' target='_blank'>$value</a>";
            }
        }
        return $data;
    }
    public static function edit(int $userId, array $contacts): bool{
        $contactsData = Contacts::getAll(['user_id' => $userId]);
        foreach($contactsData as $num=>$contactData){
            $type = $contactData['type'];
            if (!isset($contacts[$type])) continue;
            if ($contacts[$type] == $contactData['contact']) {
                unset($contacts[$type]);
                continue;
            }
            Contacts::remove($contactData['id']);
        }
        if (!empty($contacts)){
            foreach($contacts as $type=>$contact){
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
}