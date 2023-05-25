# Bose Durchsage

Diese Instanz gibt von AWS Polly erzeugte Audiodaten auf einen Bose Lautsprecher aus.

Für dieses Modul besteht kein Anspruch auf Fehlerfreiheit, Weiterentwicklung, sonstige Unterstützung oder Support.  
Bevor das Modul installiert wird, sollte unbedingt ein Backup von IP-Symcon durchgeführt werden.  
Der Entwickler haftet nicht für eventuell auftretende Datenverluste oder sonstige Schäden.  
Der Nutzer stimmt den o.a. Bedingungen, sowie den Lizenzbedingungen ausdrücklich zu. 

# Voraussetzungen

- AWS Polly
- Bose SoundTouch
- Bose Home/Smart Speaker

## Funktionen

Mit dieser Funktion kann eine Durchsage abgespielt werden.

```text
boolean BDS_Play(integer $InstanceID, string $Text);
```

Konnte der Befehl erfolgreich ausgeführt werden, liefert er als Ergebnis `TRUE`, andernfalls `FALSE`.

| Parameter    | Beschreibung   | Wert                         |
|--------------|----------------|------------------------------|
| `InstanceID` | ID der Instanz | z.B. 12345                   |
| `Text`       | Text           | z.B. Dies ist eine Durchsage |


**Beispiel:**

```php
$id = 12345;
$result = BDS_Play($id, 'Dies ist eine Durchsage');
var_dump($result);
```