package main

import (
  "flag"
  "fmt"
  "encoding/hex"
	"github.com/martinlindhe/gogost/gost3410"
)

func main() {
  var key string
  var tr string
  flag.StringVar(&key, "key", "", "key")
  flag.StringVar(&tr, "tx", "", "tx")
  flag.Parse()

  fmt.Println(Sign(key, []byte(tr)))
}

func Sign(rawPrivateKey string, message []byte) []byte {
	curve, _ := gost3410.NewCurveFromParams(gost3410.CurveParamsGostR34102012TC26ParamSetA)
	hexPrivateKey, err := hex.DecodeString(rawPrivateKey)
	if err != nil {
		return nil
	}
	privateKey, _ := gost3410.NewPrivateKey(curve, gost3410.Mode2012, hexPrivateKey)
	sign, _ := privateKey.SignDigest(Hash(message), rand.Reader)

	return sign
}
