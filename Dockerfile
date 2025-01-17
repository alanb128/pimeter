FROM balenalib/rpi-raspbian

ENTRYPOINT []

RUN apt-get -q update && \
        apt-get -qy install python3 \
        python3-pip python3-dev \
        gcc make

RUN pip3 install --upgrade setuptools && \
        pip3 install rpi.gpio && \
        pip3 install paho-mqtt && \
        pip3 install python-dateutil

CMD ["python3","watermeter.py"]
