# Shared reverse-proxy headers — forwards real host/scheme/IP so each service
# builds correct absolute URLs, sees the true client IP in its activity log,
# and the drivenow_token SSO cookie rides every hop.
proxy_http_version 1.1;
proxy_set_header Host               $host;
proxy_set_header X-Real-IP          $remote_addr;
proxy_set_header X-Forwarded-For    $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto  $scheme;
proxy_set_header X-Forwarded-Host   $host;
proxy_set_header Upgrade            $http_upgrade;
proxy_set_header Connection         $connection_upgrade;
client_max_body_size 20M;
proxy_read_timeout   60s;
proxy_connect_timeout 5s;
