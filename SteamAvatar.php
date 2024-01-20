<?php

enum AvatarType
{
    case ICON;
    case MEDIUM;
    case FULL;
}

class SteamAvatar
{
    private $default_avatar = "https://avatars.cloudflare.steamstatic.com/fef49e7fa7e1997310d705b2a6158ff8dc1cdfeb_full.jpg";
    
    public function __construct($profile_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://steamcommunity.com/profiles/' . $this->steamid_to_64($profile_id) . '?xml=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $avatar = $this->get_avatar($output, AvatarType::FULL);
        curl_close($ch);
        //header("Location: " . ($this->image_exists($avatar) ? $avatar : $this->default_avatar));
        return ($this->image_exists($avatar) ? $avatar : $this->default_avatar);
        
    }

    private function steamid_to_64($steam_id)
    {
        preg_match("/^STEAM_[0-5]:[01]:\d+$/i", $steam_id, $match);
        if (empty($match)) {
            return null;
        }
        $split = explode(":", $steam_id);
        $v = 76561197960265728;
        $y = intval($split[1]);
        $z = intval($split[2]);
        $w = ($z * 2) + $v + $y;
        return $w;
    }

    private function get_avatar($xml_string, $avatar_type)
    {
        $xml = simplexml_load_string($xml_string);

        switch ($avatar_type) {
            case AvatarType::ICON:
                $avatar = (string) $xml->avatarIcon;
                break;
            case AvatarType::MEDIUM:
                $avatar = (string) $xml->avatarMedium;
                break;
            case AvatarType::FULL:
                $avatar = (string) $xml->avatarFull;
                break;
            default:
                throw new InvalidArgumentException('Invalid avatar type');
        }

        return $avatar;
    }

    private function image_exists($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return $responseCode === 200;
    }
}

?>
