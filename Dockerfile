FROM bitnami/laravel:latest
RUN apt-get update
RUN apt-get install -y curl libc-ares-dev build-essential git python python3 ffmpeg
WORKDIR /
RUN git clone https://chromium.googlesource.com/chromium/tools/depot_tools.git
ENV PATH "$PATH:/depot_tools"
WORKDIR /shaka_packager
RUN gclient config https://www.github.com/google/shaka-packager.git --name=src --unmanaged
RUN gclient sync
WORKDIR /shaka_packager/src
RUN ninja -C out/Release; while [ $? -ne 0 ]; do ninja -C out/Release; done
ENV PATH "$PATH:/shaka_packager/src/out/Release"
WORKDIR /app

