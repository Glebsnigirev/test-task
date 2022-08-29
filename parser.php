<?php
if (empty($argv[1])) {
    echo "path is empty". PHP_EOL;
    exit(1);
}
$file = fopen($argv[1], "r");
if ($file === false) {
    echo "could not open file ". $argv[1]. PHP_EOL;
    exit(1);
}
$views = 0;
$traffic = 0;
$statusCodes = [];
$urls = [];
$crawlers = [
    "Google" => 0,
    "Bing" => 0,
    "Baidu" => 0,
    "Yandex" => 0,
];
while ($row = fgets($file, 4096)) {
    $views++;
    $data = combinedLogFormat($row);
    $traffic += (int) $data["body_bytes_sent"]; 
    if (isset($statusCodes[$data["status_code"]])) {
        $statusCodes[$data["status_code"]]++;
    } else {
        $statusCodes[$data["status_code"]] = 1;
    }
    $urls[$data['path']] = 1;
    if (stripos($data['http_user_agent'], "GoogleBot") !== false) {
        $crawlers['Google']++;
    }
    if (strpos($data['http_user_agent'], "YandexBot") !== false) {
        $crawlers['Yandex']++;
    }
    if (strpos($data['http_user_agent'], "BingBot") !== false) {
        $crawlers['Bing']++;
    }
    if (strpos($data['http_user_agent'], "Baiduspider")) {
        $crawlers['Baidu']++;
    }
}
fclose($file);
$result = [
    "views" => $views,
    "urls" => count($urls),
    "traffic" => $traffic,
    "crawlers" => $crawlers,
    "statusCodes" => $statusCodes,
];
echo json_encode($result). PHP_EOL;

function combinedLogFormat(string $row): array
{
    $result = [];
    preg_match(
        "/(?P<remote_addr>((?:[0-9]{1,3}\.){3}[0-9]{1,3})) (?P<dash>\S+) (?P<remote_user>\S+) \[(?P<time_local>[\w:\/]+\s[+|-]\d{4})\] \"(?P<request>\S+)\s?(?P<path>\S+)?\s?(?P<protocol>\S+)?\" (?P<status_code>\d{3}|-) (?P<body_bytes_sent>\d+|-)\s?\"?(?P<http_referer>[^\"]*)\"?\s\"?(?P<http_user_agent>[^\"]*)\"\s\"?(?P<http_x_forwarded_for>[^\"]*)/m",
        $row,
        $result
    );

    return $result;
}
