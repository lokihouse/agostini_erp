<div>
    @assets
    <script type="text/javascript" src="https://unpkg.com/@zxing/library@latest"></script>
    <script type="text/javascript" src="https://unpkg.com/@zxing/browser@latest"></script>
    @endassets

    <div x-data="{
        codeReader: null,
        async init() {
            this.codeReader = new ZXingBrowser.BrowserMultiFormatOneDReader();
            let videoInputDevices = await ZXingBrowser.BrowserCodeReader.listVideoInputDevices();
            let backLabeledVideos = videoInputDevices.filter(video => video.label.includes('back'));
            videos = backLabeledVideos.length > 0 ? backLabeledVideos : videoInputDevices;

            this.startDecoding(videos);
        },
        async startDecoding(videoList){
            if(videoList.length === 0) {
                return;
            }
            this.codeReader.decodeFromVideoDevice(videoList[0].deviceId, document.querySelector('video'), (result, err, control) => {
                if (result) {
                    console.log(result);
                    alert(result.text);
                }
            }).catch (e => {
                videoList.shift();
                this.startDecoding(videoList);
            })
        },
    }">
        <div class="relative">
            <video id="video" height="150" class="rounded-lg"></video>
        </div>
    </div>
</div>
