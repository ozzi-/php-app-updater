cat repo/$1 | sha512sum | cut -d " " -f 1 > hash
openssl rsautl -sign -inkey private_key.pem -keyform PEM -in hash | base64 -w 0  > signature
cat signature
