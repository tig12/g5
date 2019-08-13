/** 

    Bridge to use github.com/soniakeys/meeus/eqtime from php

    @history    2019-07-29 01:38:30+02:00, Thierry Graff : Creation
**/
package main                

import (
	"fmt"
	"math"
	"os"
	"strconv"
	"github.com/soniakeys/meeus/eqtime"
	"github.com/soniakeys/meeus/julian"
)

var err error
/** 
    Prints the equation of time from y, m, d arguments passed on the command line
    @return     Eq of time, in time seconds.
**/
func main() {
    y64, err := strconv.ParseInt(os.Args[1], 10, 16)
    if err != nil { panic(err) }
	y := int(y64)
    m64, err := strconv.ParseInt(os.Args[2], 10, 8)
	if err != nil { panic(err) }
	m := int(m64)
    d, err := strconv.ParseFloat(os.Args[3], 64)
	if err != nil { panic(err) }
	eq := eqtime.ESmart(julian.CalendarGregorianToJD(y, m, d))
	// conversion to time seconds
	// 43200 = 24 * 3600 / 2
	fmt.Printf("%f", eq * 43200 / math.Pi)
}
