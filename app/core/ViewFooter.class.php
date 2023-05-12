<?

namespace app\core;

use app\models\Settings;

class ViewFooter {
    
    public static function get(){
        $contacts = Settings::getGroup('contacts');

        $footerGmapLink = $contacts['gmap_link']['value'];
        $footerAdress = '<p>'.str_replace('  ', '</p><p>',$contacts['adress']['value']).'</p>';
        
        $footerContacts = '';
        if (isset($contacts['telegram']) && !empty($contacts['telegram']['value'])){
            $footerContacts .= "<p><a class='fa fa-telegram' href='{$contacts['telegram']['value']}' target='_blank'> {$contacts['tg-name']['value']}</a></p>";
        }
        if (isset($contacts['email']) && !empty($contacts['email']['value'])){
            $footerContacts .= "<p><a class='fa fa-envelope' href='mailto:{$contacts['email']['value']}' target='_blank'> {$contacts['email']['value']}</a></p>";
        }
        if (isset($contacts['phone']) && !empty($contacts['phone']['value'])){
            $footerContacts .= "<p><a class='fa fa-phone' href='tel:{$contacts['phone']['value']}' target='_blank'></a> {$contacts['phone']['value']}</p>";
        }
        $footerGmapWidget = $contacts['gmap_widget']['value'];
        
        $socials = Settings::getGroup('socials');
        $footerSocials = '';
        if (isset($socials['facebook']) && !empty($socials['telegram']['value'])){
            $footerSocials .= "<a class='fa fa-facebook-square' href='{$socials['facebook']['value']}' target='_blank'></a>";
        }
        if (isset($socials['youtube']) && !empty($socials['youtube']['value'])){
            $footerSocials .= "<a class='fa fa-youtube-square' href='{$socials['youtube']['value']}' target='_blank'></a>";
        }
        if (isset($socials['instagram']) && !empty($socials['instagram']['value'])){
            $footerSocials .= "<a class='fa fa-instagram' href='{$socials['instagram']['value']}' target='_blank'></a>";
        }

        return compact('footerGmapLink', 'footerAdress', 'footerContacts', 'footerGmapWidget', 'footerSocials');
    }
}