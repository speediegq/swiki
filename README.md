# swiki

Simple, suckless read-only wiki

## Features

- Simple
  - swiki only requires PHP. It has no JavaScript, no SQL and doesn't
  **require** a web server or any other dependencies.
- Minimal
  - swiki doesn't have a thousand different features. It simply displays
  text and images to the user. Nothing else.
- Markdown articles
  - Markdown is a simple format that people can understand even with no
  HTML/CSS or PHP knowledge.
- Read-only
  - swiki is read only, meaning the user cannot make any changes using
  a web interface. This is better, why implement a new editor when the
  user can just clone a repository in their existing editor and push.
  Contributions should be made using a separate tool, such as Git.

## Setup

Install PHP and clone the repository and point your web server to `index.php`.
To test locally, you can run `php` with the `-S` argument.

## Preview

See the wiki in action [here](https://spmenu.speedie.site).

## License

MIT License

Copyright (c) 2013 Steven Frank
Copyright (c) 2023 speedie <speedie@speedie.site>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
