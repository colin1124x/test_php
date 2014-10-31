<?php

// -------------------------
//echo hybi10_encode('654', 'text', false), PHP_EOL;
//die;
// -------------------------

ob_implicit_flush(true);
$context = stream_context_create();
//dd($context);
$master = stream_socket_server("tcp://127.0.0.1:8001", $errno, $err, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN, $context);
//dd($context, $master);
$all_sockets = array($master);
$clients = array();
while (true) {
    $read = $all_sockets;
    @stream_select($read, $write = null, $except = null, 0, 5000);

    foreach ($read as $socket) {
        say('trace: '.print_r($socket, true));
        if ($socket == $master) {
            say('master is: '.print_r($socket, true));
            if (($ressource = stream_socket_accept($master)) === false) {
                say('Socket error: '.socket_strerror(socket_last_error($ressource)));
            } else {
                $client_name = stream_socket_get_name($ressource, true);
                $socket_names[] = $client_name;
                say('Client: '.$client_name);
                $all_sockets[] = $ressource;
                $rs_id = (int)$ressource;
                $clients[$rs_id] = array(
                    'id' => $rs_id,
                    'name' => $client_name,
                    'socket' => $ressource,
                    'handshaked' => false,
                );
            }
        } else {
            $socket_id = (int) $socket;
            if ( ! isset($clients[$socket_id])) {
                continue;
            }

            say("socket id is [{$socket_id}]");
            say('client: '.print_r($clients[$socket_id], true));

            $data = read_buffer($socket);

            if (0 == strlen($socket_id)) {
                say('disconnected...');
                // normal close
                $payload = str_split(sprintf('%016b', 1000), 8);
                $payload[0] = chr(bindec($payload[0]));
                $payload[1] = chr(bindec($payload[1]));
                $payload = join('', $payload).'normal closure';
                hybi10_encode($payload, 'close', false);
                continue;
            }

            if ($clients[$socket_id]['handshaked']) {
                $parsed = hybi10_decode($data);

                if ( ! $parsed) {
                    continue;
                }

                switch ($parsed['type']) {
                    case 'text':
                        say('data: '.$parsed['payload']);

                        $encode = hybi10_encode($parsed['payload'], 'text', false);

                        say('data encode: '.$encode);

                        array_map(function($c) use($encode, $socket_id){
                                say("writing to: [{$socket_id}] => {$encode}");
                                $r = write_buffer($c['socket'], $encode);
                                say('wrote on client send... '.print_r($r, true));
                            }, $clients);
                    break;
                }

            } else {
                $headers = array();
                preg_replace_callback('/(\S+): (.*)/', function($m) use(&$headers) {
                    $headers[$m[1]] = trim($m[2]);
                }, $data);
                say('headers: '.print_r($headers, true));

                if ( ! isset($headers['Sec-WebSocket-Key'])) {
                    unset($clients[(int)$socket]);
                    continue;
                }
                $sec_key = $headers['Sec-WebSocket-Key'];
                $sec_accept = base64_encode(pack('H*', sha1($sec_key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

                $response = array();
                $response[] = 'HTTP/1.1 101 Switching Protocols';
                $response[] = 'Upgrade: websocket';
                $response[] = 'Connection: Upgrade';
                $response[] = "Sec-WebSocket-Accept: {$sec_accept}";

                say('writing buffer...');
                if ($written = write_buffer($socket, join("\r\n", $response)."\r\n\r\n")) {

                }
                $clients[$socket_id]['handshaked'] = true;
                say('wrote... '.print_r($written, true));
            }
        }
    }
}

function dd()
{
    call_user_func_array('var_dump', func_get_args());
    die;
}

function say($msg)
{
    echo ">> {$msg}", PHP_EOL;
}

function read_buffer($socket)
{
    $buffer = [];
    $buffsize = 8192;
    $metadata['unread_bytes'] = 0;
    do {
        if (feof($socket)) {
            return false;
        }

        $s = fread($socket, $buffsize);
        if ($s === false || feof($socket)) {
            return false;
        }
        $buffer[] = $s;
        $metadata = stream_get_meta_data($socket);
        $buffsize = ($metadata['unread_bytes'] > $buffsize) ? $buffsize : $metadata['unread_bytes'];
    } while($metadata['unread_bytes'] > 0);

    return join('', $buffer);
}

function write_buffer($socket, $string)
{
    $leng = strlen($string);
    for ($written = 0; $written < $leng; $written += $fwrite) {
        $fwrite = @fwrite($socket, $s = substr($string, $written));
        if (false === $fwrite || 0 === $written) {
            return false;
        }

        say('write: '.$s);
    }

    return $written;
}

function hybi10_encode($payload, $type = 'text', $masked = true)
{
    $frame_head = array();
    $frame = '';
    $payload_length = strlen($payload);

    switch ($type) {
        case 'text':
            $frame_head[0] = 129;//10000001
        break;

        case 'close':
            $frame_head[0] = 136;//10001000
        break;

        case 'ping':
            $frame_head[0] = 137;//10001001
        break;

        case 'pong':
            $frame_head[0] = 138;//10001010
        break;
    }

    if (65535 < $payload_length) {
        $payload_length_bin = str_split(sprintf('%064b', $payload_length), 8);
        $frame_head[1] = (true === $masked) ? 255 : 127;

        for ($i = 0; $i < 8; $i++) {
            $frame_head[$i+2] = bindec($payload_length_bin[$i]);
        }

        if (127 < $frame_head[2]) {
            // @todo close
            return false;
        }
    } elseif (125 < $payload_length) {
        $payload_length_bin = str_split(sprintf('%016b', $payload_length), 8);
        $frame_head[1] = (true === $masked) ? 254 : 126;
        $frame_head[2] = bindec($payload_length_bin[0]);
        $frame_head[3] = bindec($payload_length_bin[1]);
    } else {
        $frame_head[1] = (true === $masked) ? $payload_length + 128 : $payload_length;
    }

    foreach (array_keys($frame_head) as $i) {
        $frame_head[$i] = chr($frame_head[$i]);
    }

    if (true === $masked) {
        $mask = array();
        for ($i = 0; $i < 4; $i++) {
            $mask[$i] = chr(rand(0, 255));
        }

        $frame_head = array_merge($frame_head, $mask);
    }

    $frame = join('', $frame_head);

    for ($i = 0; $i < $payload_length; $i++) {
        $frame .= (true === $masked) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
    }

    return $frame;
}

function hybi10_decode($data)
{
    $payload_length = '';
    $mask = '';
    $unmasked_playload = '';
    $decode_data = array();

    $first_byte_binary = sprintf('%08b', ord($data[0]));
    $second_byte_binary = sprintf('%08b', ord($data[1]));
    $opcode = bindec(substr($first_byte_binary, 4, 4));
    $is_masked = $second_byte_binary[0] == '1';
    $payload_length = ord($data[1]) & 127;

    if (false === $is_masked) {
        // @todo close
    }

    switch ($opcode) {
        case 1:
            $decode_data['type'] = 'text';
        break;

        case 2:
            $decode_data['type'] = 'binary';
        break;

        case 8:
            $decode_data['type'] = 'close';
        break;

        case 9:
            $decode_data['type'] = 'ping';
        break;

        case 10:
            $decode_data['type'] = 'pong';
        break;

        default:
            // @todo close
    }

    if (126 === $payload_length) {
        $mask = substr($data, 4, 4);
        $payload_offset = 8;
        $data_length = bindec(sprintf('%08b', ord($data[2])).sprintf('%08b', ord($data[3]))) + $payload_offset;
    } elseif (127 === $payload_length) {
        $mask = substr($data, 10, 4);
        $payload_offset = 14;
        $tmp = '';
        for ($i = 0; $i < 8; $i++) {
            $tmp .= sprintf('%08b', ord($data[$i+2]));
        }
        $data_length = bindec($tmp) + $payload_offset;
        unset($tmp);
    } else {
        $mask = substr($data, 2, 4);
        $payload_offset = 6;
        $data_length = $payload_length + $payload_offset;
    }

    if (strlen($data) < $data_length) {
        return false;
    }

    if (true === $is_masked) {
        for ($i = $payload_offset; $i < $data_length; $i++) {
            $j = $i - $payload_offset;
            if (isset($data[$i])) {
                $unmasked_playload .= $data[$i] ^ $mask[$j % 4];
            }
        }
        $decode_data['payload'] = $unmasked_playload;
    } else {
        $payload_offset = $payload_offset -4;
        $decode_data['payload'] = substr($data, $payload_offset);
    }

    return $decode_data;
}
