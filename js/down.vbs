Set objDOM = WScript.GetObject(WScript.Arguments(0))
Do Until objDOM.ReadyState = "complete"
WScript.Sleep 100
Loop
WScript.Echo objDOM.DocumentElement.OuterText
