# Bakalárska práca 2017/2018 VUT FIT Brno
## Street view pomocou mobilnej senzorickej platformy

### Informácie

Priečinok obsahuje dve aplikácie

  - /video_recording
  - /virtual_tours

### Video Recording

Táto aplikácia je spustiteľná iba s príslušnými hardvérovými zariadeniami a softvérovými aplikáciami. Aplikácia bola integrovaná do senzorickej platformy na mapovanie budov.

Potrebné knižnice:

  - libgphoto2: http://www.gphoto.org/proj/libgphoto2/
  - libmtp: http://libmtp.sourceforge.net/

Ďalší potrebný harvér a softvér:
  - Richoh Theta S
  - Velodyne LiDAR
  - ROS
  - ...

### Virtual Tour

Táto aplikácia bola testovaná na zariadení Xampp. Na serveri je potrebné nastaviť limit veľkosti nahrávaných súborov a povolenia pre aplikáciu *ffmpeg*. 
Ďalej je potrebná inštalácia spomínanej aplikácie *ffmpeg*: https://www.ffmpeg.org/ 
a aplikácie na zobrazovanie panoramatických fotografií *pannellum*: https://pannellum.org/ 
Aplikácia *pannellum* musí byť umiestnená v rovnakom priečinku ako zdrojové súbory.
