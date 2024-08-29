# The RAGged Edge Box

Welcome to RAGged Edge Box, an open-source Retrieval Augmented
Generation (RAG) system designed to help you find and manage your
confidential documents with ease. The state-of-the-art semantic search
and AI-powered question-answering techniques make it simple to locate
and access the information you need, all within the privacy of your
own laptop.

Learn more: [https://textualization.com/ragged/](https://textualization.com/ragged/)

# Building

To build the image you'll need:

* Debian GNU/Linux
* git
* wget
* Docker

```bash
./get-dependencies.sh
```

as root (or using `sudo`):

```bash
./make-image.sh
```

The final output is `RAGged_Edge_Box.ova` in the current folder.

Make sure you have plenty of disk available for Docker and the intermediate disk images.

# Running locally

Under a Debian GNU/Linux bookworm install, compile the dependencies following the first image in the [`Dockerfile`](Dockerfile). That includes installing the dependencies system-wide.

Copy the SQLite3 extension binaries (`vss0.so` and `vector0.so`) to `/usr/local/lib/sqlite3` (you might need to create the folder), then set `sqlite3.extension_dir` to `/usr/local/lib/sqlite3` under `[sqlite3]` in the `php.ini` for your web server (e.g., `/etc/php/8.2/apache2/php.ini`).

You might want to set other entries in your web server `php.ini` according to [`box/php.ini`](box/php.ini) in this repo (enabling the `pdo_sqlite` and `ffi` extensions, increasing memory limits, execution times, file upload sizes, etc).

Install the composer dependecies following the relevant entries in the  [`Dockerfile`](Dockerfile) (`composer install` and subsequent lines).

Then set a symlink from `/var/www/ragged` to the `site/` folder in this repo.

RAGged Edge Box will be available at [http://localhost/ragged](http://localhost/ragged).

To have the LLM running, build [`llama.cpp`](llama.cpp) following the instructions in the  [`Dockerfile`](Dockerfile) (you only need to compile the binary version for your local CPU) and launch it following [`box/launch-llama.sh`](box/launch-llama.sh) (the GGUF model will be located someplace else, probably in [`download`](download) folder if you had run [`make-image.sh`](make-image.sh).


