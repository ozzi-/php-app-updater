if [ "$#" -lt 4 ]
  then
  echo "Usage: updateDB.sh {buildid} {version} {filename} {releasenotes} {optional: write to file}"
  exit -1
fi

sig=$(./createSignature.sh $3)

if [ -z "$5" ]
  then
  cat db.json | jq '. += [{"buildid":'$1', "version":"'$2'", "filename":"'$3'", "releasenotes":"'$4'", "signature": "'$sig'"}]'
else
  cat db.json | jq '. += [{"buildid":'$1', "version":"'$2'", "filename":"'$3'", "releasenotes":"'$4'", "signature": "'$sig'"}]' > db.json.new
  cp -rf db.json.new db.json
  rm -rf db.json.new
  echo "Updated db.json"
fi

