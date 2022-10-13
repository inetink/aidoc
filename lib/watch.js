"use strict";


let cwd = process.cwd();
const fs = require('fs-extra');
const path = require('path');

function removeDirOrFile(p) {
    if (fs.existsSync(p)) {
        if (fs.statSync(p).isDirectory()) {
            let f = fs.readdirSync(p);
            for (var i = 0; i < f.length; i++) {
                let tmp = path.join(p, f[i]);
                if (fs.statSync(tmp).isDirectory()) {
                    removeDirOrFile(tmp);
                } else {
                    fs.unlinkSync(tmp);
                }
            }
            fs.rmdirSync(p);
        } else {
            fs.unlinkSync(p);
        }
    }
}

const _require = require('child_process'),
    fork = _require.fork;

module.exports = (dev) => {
    process.env.UMI_PRESETS = require.resolve('@umijs/preset-dumi'); // start umi use child process

    // console.log("##########", process.argv)
    // console.log("##########", process.argv.slice(2))

    if (!dev) {
        dev = 'dev'
    }

    const child = fork(require.resolve('umi/bin/umi'), [...([dev] || [])], {
        stdio: 'inherit'
    }); // handle exit signals

    child.on('exit', (code, signal) => {
        if (signal === 'SIGABRT') {
            process.exit(1);
        }


        if (fs.existsSync(path.join(cwd, ".umirc.ts"))) {
            fs.unlinkSync(path.join(cwd, ".umirc.ts"))
        }
        if (fs.existsSync(path.join(cwd, ".umi"))) {
            removeDirOrFile(path.join(cwd, ".umi"))
        }
        if (fs.existsSync(path.join(cwd, ".umi-production"))) {
            removeDirOrFile(path.join(cwd, ".umi-production"))
        }


        process.exit(code);
    }); // for e2e test

    child.on('message', args => {
        if (process.send) {
            process.send(args);
        }
    });


    process.on('SIGINT', () => {
        child.kill('SIGINT');
    });
    process.on('SIGTERM', () => {
        child.kill('SIGTERM');
    });


};
