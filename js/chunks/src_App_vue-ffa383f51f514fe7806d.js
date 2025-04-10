/*! For license information please see src_App_vue-ffa383f51f514fe7806d.js.LICENSE.txt */
"use strict";(self.webpackChunkbav=self.webpackChunkbav||[]).push([["src_App_vue"],{1949:(t,e,n)=>{n.r(e),n.d(e,{default:()=>m});var a=n(5471),o=n(1827),s=n(9498);const c=(t,e,n)=>{let a=(0,s.Jv)("/apps/"+o.J+"/"+t,e,n);const c={...e||{}};for(const e of t.matchAll(/{([^{}]*)}/g))delete c[e[1]];const i=[];for(const[t,e]of Object.entries(c))try{i.push(t+"="+encodeURIComponent(e?.toString()||""))}catch(t){console.debug("STRING CONVERSION ERROR",t)}return i.length>0&&(a+="?"+i.join("&")),a};var i=n(3334),l=n(6913),r=n(9077),u=n(6091),p=n(3431);const d=(0,a.pM)({__name:"BankAccountInputMask",props:{bankAccount:null},emits:["update:bankAccount","blur:account-field"],setup(t,{emit:e}){const n=t;console.info("BAV PROPS",{props:n});const s=(0,a.Kh)(n.bankAccount),c=Object.keys(n.bankAccount).filter((t=>!t.startsWith("_")));(0,a.wB)(n,(()=>{for(const t of c)s[t]!==n.bankAccount[t]&&(console.info("BAV PROPS WATCHER",{key:t,old:s[t],new:n.bankAccount[t]}),s[t]=n.bankAccount[t])}));const l=t=>{e("update:bankAccount",{changed:t,data:s})};for(const t of c)(0,a.wB)((()=>s[t]),(()=>l(t)));return{__sfc:!0,props:n,accountData:s,emit:e,onBlur:t=>{e("blur:account-field",{[t]:s[t]})},accountKeys:c,emitUpdate:l,appName:o.J,t:i.Tl,NcTextField:r.v}}});var h=n(4486);const f=(0,h.A)(d,(function(){var t=this,e=t._self._c,n=t._self._setupProxy;return e("div",{staticClass:"container flex flex-column"},[e(n.NcTextField,{attrs:{value:n.accountData.IBAN,type:"text",name:"bankAccountIBAN",placeholder:"DE48123456780123456789",label:n.t(n.appName,"IBAN")},on:{"update:value":function(e){return t.$set(n.accountData,"IBAN",e)},blur:function(t){return n.onBlur("IBAN")}}}),t._v(" "),e(n.NcTextField,{attrs:{value:n.accountData.BIC,type:"text",name:"bankAccountBIC",placeholder:"XYBLAHDEFXX",label:n.t(n.appName,"BIC")},on:{"update:value":function(e){return t.$set(n.accountData,"BIC",e)},blur:function(t){return n.onBlur("BIC")}}}),t._v(" "),e(n.NcTextField,{attrs:{value:n.accountData.bankId,type:"text",name:"bankAccountBankId",placeholder:"12345678",label:n.t(n.appName,"Bank Id")},on:{"update:value":function(e){return t.$set(n.accountData,"bankId",e)},blur:function(t){return n.onBlur("bankId")}}}),t._v(" "),e(n.NcTextField,{attrs:{value:n.accountData.accountId,type:"text",name:"bankAccountId",placeholder:"0123456789",label:n.t(n.appName,"Bank Account Id")},on:{"update:value":function(e){return t.$set(n.accountData,"accountId",e)},blur:function(t){return n.onBlur("accountId")}}}),t._v(" "),e(n.NcTextField,{attrs:{value:n.accountData.bankName,type:"text",readonly:"",name:"bankAccountBankName",placeholder:"",label:n.t(n.appName,"Bank")},on:{"update:value":function(e){return t.$set(n.accountData,"bankName",e)}}})],1)}),[],!1,null,"53a9c702",null).exports;var v=n(1083);const b=t=>v.A.isAxiosError(t),g=(0,a.pM)({__name:"App",setup(t,{expose:e}){const n=(0,a.KR)(!0),s=(0,a.KR)(!1),d=(0,a.KR)(null),h=(0,a.KR)([]),v=(0,a.KR)({}),g=(0,a.EW)((()=>Object.keys(v.value).length)),m={IBAN:"",BIC:"",bankId:"",accountId:"",bankName:""},y=Object.keys(m),k=(0,a.Kh)({...m}),_=t=>y.reduce(((e,n)=>e&&!t[n]),!0),A=t=>y.reduce(((e,n)=>e&&!!t[n]),!0),N=(0,a.EW)((()=>_(k))),B=(0,a.KR)([]),I=(0,a.KR)(-1),x=(0,a.EW)((()=>I.value<=0)),w=(0,a.EW)((()=>I.value>=B.value.length-1)),D=(0,a.EW)((()=>I.value>=0&&I.value<B.value.length?B.value[I.value]:null)),T=t=>{if(!_(t)){if(D.value){let e=!0;for(const n of y)if(D.value[n]!==t[n]){e=!1;break}if(e)return}B.value.splice(I.value+1,1/0,{...t}),++I.value}},C=()=>{Object.assign(k,m)},R=c("validate");let O=new AbortController;const S=(t,e=4)=>{const n=new RegExp(".{1,"+e+"}","gs");return t.match(n).join(" ")},U=(t,e)=>{const n=[];let a=0,o=0,s=!0;e=S(e.replace(" ",""));for(let c=0;c<Math.min(t.length,e.length);++c)if(t[c]===e[c]===s)++o;else{const e={str:t.substring(a,a+o),equal:s,start:a,len:o};n.push(e),s=!s,a=c,o=1}if(o>0){const e={str:t.substring(a),equal:s,start:a,len:o};n.push(e)}return n},E=async({data:t,changed:e,liveUpdate:n})=>{try{O.abort(),O=new AbortController;const a=await l.Ay.post(R,t,{signal:O.signal});for(const t of Object.keys(m))n&&t===e||k[t]!==a.data[t]&&(k[t]=a.data[t]);h.value=a.data.messages,v.value=Object.fromEntries(Object.entries(a.data.suggestions).map((([t,e])=>[t,U(e,k.IBAN)]))),(!n||A(k)&&0===Object.keys(v.value).length)&&T(k)}catch(t){if(b(t)&&"CanceledError"===t.name)console.debug("VALIDATION CANCELED BY USER INPUT");else{console.error("BAV VALIDATION ERROR",{error:t});let e=(0,i.Tl)(o.J,"reason unknown");if(b(a=t)&&a.response&&t.response.data){const n=t.response.data;Array.isArray(n.messages)&&(e=n.messages.join(" "))}(0,p.Qg)((0,i.Tl)(o.J,"Unable to validate the given bank account data: {message}",{message:e}))}}var a},G=t=>{n.value=t},P=()=>n.value;let F=!1;(0,a.sV)((()=>{F=!0})),(0,a.hi)((()=>{F=!1}));const L=()=>F;e({setVisibility:G,getVisibility:P,getMounted:L});const j=(0,a.Kh)({title:(0,i.Tl)(o.J,"This is a front-end to the German Bank Account Validator (BAV) developed my M. Malkusch."),backendStatus:(0,i.Tl)(o.J,"The backend is no longer actively maintained, but there are some forks on GitHub ({github}).\nHowever, it still seems to work for the majority of German bank accounts.",{github:"https://github.com/bav-php/bav/forks"}),policy:(0,i.Tl)(o.J,"Data entered will not be stored. BAV is able to compute\nthe BIC given the bank-id, and to compute the IBAN\ngiven additionally the bank-account id.\nAdditionally, it verifies that the bank-id exists\nby comparing it with the data provided by the Deutsche Bundesbank.\nIt will also pick the correct check-sum formula for the given\nbank-account id (there are a few hundrets of possible\ncheck-sum formulas, differing from bank to bank, all archived and publically\navailable at the Deutsche Bundesbank).\nFinally, given an IBAN, it will extract the bank-id and bank-account-id\nand also perform the check-sum computations in order to test whether the\nprovided bank-account data is consistent."),checksumWarning:(0,i.Tl)(o.J,"Please keep in mind that check-sums\ncannot catch all possible errors."),disclaimer:(0,i.Tl)(o.J,"This program is free software: you can redistribute it and/or modify\nit under the terms of the GNU Affero General Public License as published by\nthe Free Software Foundation, either version 3 of the License, or\n(at your option) any later version.\n\nThis program is distributed in the hope that it will be useful,\nbut WITHOUT ANY WARRANTY; without even the implied warranty of\nMERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\nGNU Affero General Public License for more details.\n\nYou should have received a copy of the GNU Affero General Public License\nalong with this program.  If not, see {gnuLicenses}.",{gnuLicenses:"http://www.gnu.org/licenses/"})});return{__sfc:!0,showDialog:n,showAbout:s,inputForm:d,hints:h,suggestions:v,suggestionsLength:g,accountDataDefaults:m,accountDataKeys:y,accountData:k,isEmptyData:_,isFilledData:A,emptyContent:N,historyData:B,historyPosition:I,atHistoryBottom:x,atHistoryTop:w,currentHistory:D,pushHistory:T,clearForm:C,stateHistoryGo:t=>{I.value+=t,B.value.length>0&&I.value<0?I.value=0:I.value>=B.value.length&&(I.value=B.value.length-1),D.value?Object.assign(k,D.value):C(),E({data:k,changed:null,liveUpdate:!1})},validationUrl:R,abortController:O,human:S,splitSuggestion:U,selectSuggestion:t=>{Object.assign(k,m),k.IBAN=t},validateInput:E,onUpdateBankAccount:async t=>{E({data:t.data,changed:t.changed,liveUpdate:!0})},onBlur:async t=>{const e={...k,...t},n=Object.keys(t)[0];await E({data:e,changed:n,liveUpdate:!1})},setVisibility:G,getVisibility:P,mounted:F,getMounted:L,about:j,appName:o.J,t:i.Tl,NcButton:r.x1,NcDialog:r.i$,NcPopover:r.rI,DynamicSvgIcon:u.A,BankAccountInputMask:f,appIcon:'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800">\n  <defs>\n    <linearGradient id="a" x1="520.33" x2="524.01" y1="978.59" y2="164.41" gradientUnits="userSpaceOnUse">\n      <stop offset="0" stop-color="#204aca"/>\n      <stop offset="1" stop-color="#a9bbf1" stop-opacity=".856"/>\n    </linearGradient>\n    <linearGradient id="b" x1="165.78" x2="743.18" y1="569.36" y2="569.36" gradientUnits="userSpaceOnUse">\n      <stop offset="0" stop-color="#16348e"/>\n      <stop offset="1" stop-color="#9eb2f0"/>\n    </linearGradient>\n    <linearGradient id="c" x1="513.87" x2="520.47" y1="156.17" y2="981.6" gradientTransform="translate(61.323 -72.501)scale(1.2965)" gradientUnits="userSpaceOnUse">\n      <stop offset="0" stop-color="#d8e5fb"/>\n      <stop offset="1" stop-color="#4580e9"/>\n    </linearGradient>\n  </defs>\n  <text xml:space="preserve" x="248.571" y="475.219" font-family="Arial" letter-spacing="0" style="line-height:0%" transform="translate(-97.438 -171.5)" word-spacing="0"><tspan x="248.571" y="475.219" font-size="72" style="line-height:1.25"> </tspan></text>\n  <text xml:space="preserve" x="162.857" y="169.505" font-family="Arial" letter-spacing="0" style="line-height:0%" transform="translate(-97.438 -171.5)" word-spacing="0"><tspan x="162.857" y="169.505" font-size="72" style="line-height:1.25"> </tspan></text>\n  <path fill="url(#a)" stroke="url(#b)" d="M527.03 177.16c-85.951 0-159.97 35.151-215.03 101.94-.014.018-.017.045-.031.063-43.627 52.62-72.07 118.06-86.062 194.56h-22.344a25.33 25.33 0 0 0-24.281 18.156l-11.97 40.625a25.327 25.327 0 0 0 19.032 31.906 25.3 25.3 0 0 0-7.063 11.375l-11.969 40.625a25.327 25.327 0 0 0 24.281 32.469h29.562c8.651 80.449 29.874 148.09 65.25 201.78 47.578 72.275 119.55 110.91 206.31 110.91 60.807 0 115.34-18.603 159.69-55.312l.032-.032c27.829-23.108 55.739-58.562 86.344-107.06a25.327 25.327 0 0 0-7.657-34.75L715 753.977a25.327 25.327 0 0 0-34.312 6.438c-28.09 39-51.419 64.85-66.03 75.969l-.094.062c-26.39 20.274-58.964 30.75-101.06 30.75-43.147 0-74.508-20.498-102.5-72.156l-.032-.062c-20.948-38.97-34.239-87.543-39.063-146.09h247.19A25.33 25.33 0 0 0 643.41 630.7l11.938-40.625a25.33 25.33 0 0 0-6.906-25.5 25.33 25.33 0 0 0 19.875-17.75l11.97-40.625a25.327 25.327 0 0 0-24.282-32.47h-278.56c8.3-56.28 22.512-102.22 41.844-137.69 28.94-52.601 61.935-73.842 107.75-73.843 42.735 0 71.326 11.552 92.156 33.812s34.906 57.998 39.125 110.19a25.327 25.327 0 0 0 25.25 23.28h20.281a25.327 25.327 0 0 0 25.312-25.311v-155.81a25.33 25.33 0 0 0-13.594-22.438c-61.695-32.192-124.89-48.75-188.53-48.75z" transform="translate(-62.926 -179.939)scale(1.0186)"/>\n  <text xml:space="preserve" x="203.776" y="922.783" fill="url(#c)" font-family="\'Times New Roman\'" letter-spacing="0" style="line-height:0%" transform="matrix(1.01752 0 0 1.01972 -63.955 -179.939)" word-spacing="0"><tspan x="203.776" y="922.783" font-size="1066.2" style="line-height:1.25">€</tspan></text>\n</svg>\n'}}});const m=(0,h.A)(g,(function(){var t=this,e=t._self._c,n=t._self._setupProxy;return e(n.NcDialog,{attrs:{name:n.t(n.appName,"BAV - Bank Account Validator (DE)"),size:"large","close-on-click-outside":!1,open:n.showDialog},on:{"update:open":function(t){n.showDialog=t}},scopedSlots:t._u([{key:"actions",fn:function(){return[e(n.NcButton,{attrs:{disabled:n.atHistoryBottom},on:{click:function(t){return n.stateHistoryGo(-1)}}},[t._v("\n      "+t._s(n.t(n.appName,"Undo"))+"\n    ")]),t._v(" "),e(n.NcButton,{attrs:{disabled:n.atHistoryTop},on:{click:function(t){return n.stateHistoryGo(1)}}},[t._v("\n      "+t._s(n.t(n.appName,"Redo"))+"\n    ")]),t._v(" "),e(n.NcButton,{attrs:{disabled:n.emptyContent},on:{click:n.clearForm}},[t._v("\n      "+t._s(n.t(n.appName,"Clear"))+"\n    ")]),t._v(" "),e(n.NcPopover,{attrs:{shown:n.showAbout,"focus-trap":!1},on:{"update:shown":function(t){n.showAbout=t}},scopedSlots:t._u([{key:"trigger",fn:function(){return[e(n.NcButton,{on:{click:function(t){n.showAbout=!0}}},[t._v("\n          "+t._s(n.t(n.appName,"About"))+"\n        ")])]},proxy:!0}])},[t._v(" "),e("div",{staticClass:"about"},[t._v("\n        "+t._s(n.about.title)+"\n      ")]),t._v(" "),e("div",{staticClass:"about"},[t._v("\n        "+t._s(n.about.backendStatus)+"\n      ")]),t._v(" "),e("div",{staticClass:"about"},[t._v("\n        "+t._s(n.about.policy)+"\n      ")]),t._v(" "),e("div",{staticClass:"about checksum"},[t._v("\n        "+t._s(n.about.checksumWarning)+"\n      ")]),t._v(" "),e("div",{staticClass:"about disclaimer"},[t._v("\n        "+t._s(n.about.disclaimer)+"\n      ")])]),t._v(" "),e(n.NcButton,{on:{click:function(t){n.showDialog=!1}}},[t._v("\n      "+t._s(n.t(n.appName,"Close"))+"\n    ")])]},proxy:!0},{key:"default",fn:function(){return[e(n.DynamicSvgIcon,{staticClass:"dialog-app-icon",attrs:{size:34,data:n.appIcon,title:n.t(n.appName,"app-logo")}}),t._v(" "),e(n.BankAccountInputMask,{ref:"inputForm",attrs:{"bank-account":n.accountData},on:{"update:bankAccount":n.onUpdateBankAccount,"blur:account-field":n.onBlur}}),t._v(" "),n.hints.length>0?e("h6",{staticClass:"hints"},[t._v("\n      "+t._s(n.t(n.appName,"Errors and Hints"))+"\n    ")]):t._e(),t._v(" "),t._l(n.hints,(function(n){return e("span",{staticClass:"hint"},[t._v(t._s(n)+". ")])})),t._v(" "),1===n.suggestionsLength?e("h6",[t._v("\n      "+t._s(n.t(n.appName,"Suggestion"))+"\n    ")]):t._e(),t._v(" "),n.suggestionsLength>1?e("h6",[t._v("\n      "+t._s(n.t(n.appName,"Suggestions"))+"\n    ")]):t._e(),t._v(" "),n.suggestionsLength>0?e("ul",t._l(n.suggestions,(function(a,o){return e("li",{key:o,staticClass:"suggestion"},[e("button",{on:{click:function(t){return t.preventDefault(),t.stopPropagation(),n.selectSuggestion(o)}}},[t._v("\n          "+t._s(n.t(n.appName,"use"))+"\n           \n          "),t._v(" "),t._l(a,(function(n){return e("span",{class:["iban-part",{"part-equal":n.equal,"part-different":!n.equal}]},[t._v(t._s(n.str))])}))],2)])})),0):t._e()]},proxy:!0}])})}),[],!1,null,"4787f8d9",null).exports}}]);
//# sourceMappingURL=src_App_vue-ffa383f51f514fe7806d.js.map