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
